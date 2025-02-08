#!/bin/bash

# Prompt user for domain and email
read -p "Enter your domain (e.g., example.com): " DOMAIN
read -p "Enter your email address (used for Let's Encrypt notifications): " EMAIL

# Check if Certbot is installed
if ! command -v certbot &> /dev/null; then
    echo "Certbot not found! Installing..."
    sudo apt-get update
    sudo apt-get install -y certbot
fi

# Request SSL certificate for wildcard domain using DNS validation (using certonly)
echo "Requesting wildcard SSL certificate for *.$DOMAIN..."

# Use Certbot with the manual DNS-01 challenge (certonly mode)
sudo certbot certonly -d "*.$DOMAIN" -d "$DOMAIN" \
    --manual \
    --preferred-challenges dns \
    --agree-tos \
    --email "$EMAIL"


# Check if the certificate was obtained successfully
if [ $? -eq 0 ]; then
    echo "Wildcard SSL certificate obtained successfully for *.$DOMAIN!"
    echo "Your certificate files are located at: /etc/letsencrypt/live/$DOMAIN/"
else
    echo "Failed to obtain wildcard SSL certificate for *.$DOMAIN. Please check your DNS provider and credentials."
fi
