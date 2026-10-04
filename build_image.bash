#!/bin/bash


cat << 'EOF' > Containerfile
# Use the official PHP Apache image as the base
FROM php:8.2-apache

# Copy the local position.php file into the container as index.php
COPY /home/podman-builder/PositionSizeCalc-K8s-Image-Build/position.php /var/www/html/index.php
RUN chmod 644 /var/www/html/index.php

# Expose port 80 (standard for Apache)
EXPOSE 80

EOF


podman build -t position_size_app -f Containerfile

podman login docker.io || exit 1
podman tag position_size_app position_size_app:latest
podman push localhost/position_size_app docker.io/jsanderson104/stuff:position_size-v1
