#!/bin/bash
# Debug script for agent <unknown> display issue
# Run on remote server: bash /var/www/html/areports/tools/debug_agents.sh

echo "=== aReports Agent Debug ==="
echo "Date: $(date)"
echo ""

# 1. Check agent_settings table
echo "=== 1. agent_settings table ==="
mysql -u root areports -e "SELECT * FROM agent_settings;" 2>&1
echo ""

# 2. Check queue_settings table
echo "=== 2. queue_settings table ==="
mysql -u root areports -e "SELECT queue_number, display_name FROM queue_settings;" 2>&1
echo ""

# 3. Check users and roles
echo "=== 3. Users ==="
mysql -u root areports -e "SELECT id, username, role_id, extension FROM users;" 2>&1
echo ""

# 4. Check user_queues
echo "=== 4. user_queues ==="
mysql -u root areports -e "SELECT * FROM user_queues;" 2>&1
echo ""

# 5. AMI settings
echo "=== 5. AMI settings ==="
mysql -u root areports -e "SELECT setting_key, setting_value FROM settings WHERE category='ami';" 2>&1
echo ""

# 6. Get AMI host/credentials and run QueueStatus
AMI_HOST=$(mysql -u root areports -N -e "SELECT setting_value FROM settings WHERE setting_key='host' AND category='ami';")
AMI_PORT=$(mysql -u root areports -N -e "SELECT setting_value FROM settings WHERE setting_key='port' AND category='ami';")
AMI_USER=$(mysql -u root areports -N -e "SELECT setting_value FROM settings WHERE setting_key='username' AND category='ami';")
AMI_SECRET=$(mysql -u root areports -N -e "SELECT setting_value FROM settings WHERE setting_key='secret' AND category='ami';")

echo "=== 6. AMI QueueStatus (raw) ==="
echo "Connecting to $AMI_HOST:$AMI_PORT..."
{
  echo "Action: Login"
  echo "Username: $AMI_USER"
  echo "Secret: $AMI_SECRET"
  echo ""
  sleep 1
  echo "Action: QueueStatus"
  echo ""
  sleep 3
  echo "Action: Logoff"
  echo ""
  sleep 1
} | nc -w 5 "$AMI_HOST" "$AMI_PORT" 2>&1
echo ""

echo "=== 7. AMI CoreShowChannels (raw) ==="
{
  echo "Action: Login"
  echo "Username: $AMI_USER"
  echo "Secret: $AMI_SECRET"
  echo ""
  sleep 1
  echo "Action: CoreShowChannels"
  echo ""
  sleep 3
  echo "Action: Logoff"
  echo ""
  sleep 1
} | nc -w 5 "$AMI_HOST" "$AMI_PORT" 2>&1
echo ""

# 8. Test the API endpoint directly
echo "=== 8. API /api/realtime/data response (agents section) ==="
# Get session cookie by checking if we can curl localhost
API_RESPONSE=$(curl -s -k "http://localhost/areports/api/realtime/data" 2>&1 | head -c 5000)
echo "$API_RESPONSE"
echo ""

echo "=== Debug complete ==="
