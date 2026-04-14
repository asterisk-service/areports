#!/bin/bash
# Import Asterisk flat-file queue_log into MySQL queuelog table
# Run: bash tools/import_queuelog.sh
#
# Format: timestamp|callid|queuename|agent|event|data1|data2|data3|data4|data5

LOGDIR="/var/log/asterisk"
DB="asteriskcdrdb"
TABLE="queuelog"
TMPFILE="/tmp/queuelog_import.csv"
SERVERID=""

echo "=== Queue Log Importer ==="
echo ""

# Count existing rows
EXISTING=$(mysql -u root -N -e "SELECT COUNT(*) FROM ${DB}.${TABLE};")
echo "Existing rows in ${TABLE}: ${EXISTING}"

# Collect all log files (rotated + current)
FILES=$(ls -1 ${LOGDIR}/queue_log-* ${LOGDIR}/queue_log 2>/dev/null | sort)
TOTAL_LINES=0
for f in $FILES; do
    lines=$(wc -l < "$f")
    TOTAL_LINES=$((TOTAL_LINES + lines))
done
echo "Log files found: $(echo "$FILES" | wc -w)"
echo "Total lines to process: ${TOTAL_LINES}"
echo ""

# Create temp CSV for LOAD DATA
> "$TMPFILE"

for f in $FILES; do
    echo "Processing: $f"
    while IFS='|' read -r timestamp callid queuename agent event data1 data2 data3 data4 data5 rest; do
        # Skip empty lines
        [ -z "$timestamp" ] && continue
        # Skip non-numeric timestamps
        [[ ! "$timestamp" =~ ^[0-9]+$ ]] && continue

        # Convert Unix timestamp to MySQL datetime
        datetime=$(date -d "@${timestamp}" '+%Y-%m-%d %H:%M:%S' 2>/dev/null)
        [ -z "$datetime" ] && continue

        # Clean fields - truncate to column max lengths
        callid="${callid:0:40}"
        queuename="${queuename:0:20}"
        agent="${agent:0:40}"
        event="${event:0:20}"
        data1="${data1:0:40}"
        data2="${data2:0:40}"
        data3="${data3:0:40}"
        data4="${data4:0:40}"
        data5="${data5:0:40}"

        # Write tab-separated line for LOAD DATA
        printf '%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n' \
            "$datetime" "$callid" "$queuename" "$agent" "$event" \
            "$data1" "$data2" "$data3" "$data4" "$data5" >> "$TMPFILE"
    done < "$f"
done

IMPORT_LINES=$(wc -l < "$TMPFILE")
echo ""
echo "Parsed lines ready for import: ${IMPORT_LINES}"

if [ "$IMPORT_LINES" -eq 0 ]; then
    echo "Nothing to import."
    rm -f "$TMPFILE"
    exit 0
fi

# Import via LOAD DATA
echo "Importing into ${DB}.${TABLE}..."
mysql -u root "$DB" <<EOF
LOAD DATA LOCAL INFILE '${TMPFILE}'
INTO TABLE ${TABLE}
FIELDS TERMINATED BY '\t'
LINES TERMINATED BY '\n'
(time, callid, queuename, agent, event, data1, data2, data3, data4, data5);
EOF

if [ $? -eq 0 ]; then
    NEWCOUNT=$(mysql -u root -N -e "SELECT COUNT(*) FROM ${DB}.${TABLE};")
    IMPORTED=$((NEWCOUNT - EXISTING))
    echo ""
    echo "Done! Imported ${IMPORTED} rows."
    echo "Total rows now: ${NEWCOUNT}"
else
    echo "ERROR: Import failed."
    echo "Trying row-by-row insert as fallback..."

    COUNT=0
    while IFS=$'\t' read -r datetime callid queuename agent event data1 data2 data3 data4 data5; do
        mysql -u root "$DB" -e "INSERT INTO ${TABLE} (time, callid, queuename, serverid, agent, event, data1, data2, data3, data4, data5) VALUES ('${datetime}', '${callid}', '${queuename}', '${SERVERID}', '${agent}', '${event}', '${data1}', '${data2}', '${data3}', '${data4}', '${data5}');" 2>/dev/null
        COUNT=$((COUNT + 1))
        if [ $((COUNT % 1000)) -eq 0 ]; then
            echo "  Inserted ${COUNT} rows..."
        fi
    done < "$TMPFILE"

    NEWCOUNT=$(mysql -u root -N -e "SELECT COUNT(*) FROM ${DB}.${TABLE};")
    echo "Done! Total rows: ${NEWCOUNT}"
fi

rm -f "$TMPFILE"
echo ""
echo "=== Import complete ==="
