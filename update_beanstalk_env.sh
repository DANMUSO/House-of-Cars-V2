#!/bin/bash

ENV_NAME="Finplus-Suite-Sandbox-env"

# Build env.json safely
> env.json
echo "[" >> env.json
while IFS='=' read -r key value; do
  # skip empty lines or comments
  [[ -z "$key" || "$key" =~ ^# ]] && continue
  # remove surrounding quotes and escape inner quotes
  esc_value=$(echo "$value" | sed 's/"/\\"/g')
  echo "  { \"Namespace\": \"aws:elasticbeanstalk:application:environment\", \"OptionName\": \"$key\", \"Value\": \"$esc_value\" }," >> env.json
done < .env
# remove last comma and close JSON
sed -i '$ s/},$/}/' env.json
echo "]" >> env.json

# Apply to Beanstalk
aws elasticbeanstalk update-environment \
  --region eu-central-1 \
  --environment-name "$ENV_NAME" \
  --option-settings file://env.json

