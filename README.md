# Courant: Revolutionizing Power Management for MSMEs

<div align="center">

![Courant Logo](public/logo.svg)

**Smart IoT Power Management & Predictive Maintenance System**

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![FilamentPHP](https://img.shields.io/badge/FilamentPHP-3.2-FFA500?style=flat&logo=php)](https://filamentphp.com)
[![Flask](https://img.shields.io/badge/Flask-3.1-000000?style=flat&logo=flask)](https://flask.palletsprojects.com)
[![TensorFlow](https://img.shields.io/badge/TensorFlow-2.19-FF6F00?style=flat&logo=tensorflow)](https://tensorflow.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

*Empowering Micro, Small, and Medium-sized Enterprises (MSMEs) with intelligent power management to reduce costs and prevent equipment damage*

</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [Technology Stack](#-technology-stack)
- [Hardware Requirements](#-hardware-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage Guide](#-usage-guide)
- [API Documentation](#-api-documentation)
- [Architecture](#-architecture)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Limitations & Disclaimers](#-limitations--disclaimers)
- [Contributing](#-contributing)
- [Support](#-support)
- [License](#-license)

---

## 🎯 Overview

**Courant** is a comprehensive Progressive Web Application (PWA) designed to revolutionize power management for Micro, Small, and Medium-sized Enterprises (MSMEs). By integrating IoT devices with advanced AI/ML algorithms, Courant provides real-time monitoring, predictive maintenance, and intelligent power optimization to help businesses reduce energy costs and prevent costly equipment failures.

### 🏢 Target Audience
- **MSME Owners** seeking to optimize operational costs
- **Facility Managers** responsible for equipment maintenance  
- **Energy Consultants** providing efficiency solutions
- **Industrial IoT Developers** building similar solutions

### 🎯 Core Objectives
- **Cost Reduction**: Monitor and optimize energy consumption patterns
- **Predictive Maintenance**: Detect potential electrical faults before they occur
- **Equipment Protection**: Prevent damage through real-time monitoring
- **Operational Intelligence**: Provide actionable insights for decision-making

---

## ✨ Key Features

### 📊 **Real-Time Monitoring Dashboard**
- **Live Power Consumption**: Real-time displays of voltage, current, power, and energy metrics
- **Interactive Line Graphs**: Historical data visualization with customizable time filters
- **Power Factor Analysis**: Track and optimize power efficiency
- **Multi-Device Support**: Monitor multiple EDMER devices simultaneously

### 🔮 **AI-Powered Predictive Maintenance**
- **LSTM Neural Networks**: Advanced time-series analysis for anomaly detection
- **Intelligent Fault Detection**: Identify potential electrical issues before they occur
- **Root Cause Analysis**: AI-driven insights into anomaly causes
- **Actionable Recommendations**: Specific maintenance suggestions and next steps

### 🚨 **Smart Notification System**
- **Real-Time Alerts**: Instant notifications for abnormal power usage
- **Multi-Channel Delivery**: Email, SMS, and in-app notifications
- **Customizable Thresholds**: User-defined alert criteria per appliance
- **Alert Prioritization**: Business impact-based notification routing

### 🔌 **Remote Appliance Control**
- **Socket Management**: Remote on/off control of connected devices
- **Scheduled Operations**: Automated power scheduling for optimal efficiency
- **Load Balancing**: Intelligent distribution of power loads
- **Emergency Shutoff**: Remote safety controls for critical situations

### 📋 **Comprehensive Appliance Profiling**
- **Device Registration**: Name, expected power, and threshold configuration
- **Anomaly Thresholds**: Customizable power deviation limits
- **Usage Patterns**: Historical consumption analysis per appliance
- **Performance Metrics**: Efficiency tracking and optimization suggestions

### 📈 **Advanced Reporting Suite**
- **Energy Consumption Reports**: Detailed usage analytics with cost calculations
- **Cost Analysis**: ROI tracking and savings identification
- **Equipment Usage Reports**: Performance and efficiency metrics
- **Anomaly Reports**: Comprehensive fault detection summaries
- **Export Options**: CSV and PDF format support for external analysis

### 👥 **Administrative Controls**
- **User Management**: Multi-role access control and permissions
- **Device Whitelisting**: Secure IoT device authentication via UID management
- **System Configuration**: Customizable app settings and preferences
- **Audit Logging**: Complete activity tracking for compliance

---

## 🛠 Technology Stack

### **Frontend & UI**
- **Framework**: Laravel 12.0 (PHP 8.2+)
- **Admin Interface**: FilamentPHP 3.2
- **Real-time UI**: Livewire 3.4
- **Styling**: TailwindCSS with custom components
- **PWA Support**: TomatoPHP Filament PWA

### **Backend Services**
- **Web Server**: Laravel Framework
- **AI/ML Service**: Flask 3.1 (Python)
- **Database**: MySQL with optimized indexing
- **Queue System**: Laravel Queues for background processing
- **Authentication**: Laravel Sanctum

### **AI/ML Technology**
- **Deep Learning**: TensorFlow 2.19 with Keras
- **Algorithm**: LSTM (Long Short-Term Memory) Neural Networks
- **Data Processing**: NumPy, Pandas, SciKit-Learn
- **Model Management**: Joblib for model persistence

### **IoT & Communication**
- **Protocol**: MQTT (Message Queuing Telemetry Transport)
- **Client**: PHP-MQTT Laravel Client
- **Real-time Data**: WebSocket connections for live updates

### **Development & Build Tools**
- **Frontend Build**: Vite 6.0 with Laravel plugin
- **Package Management**: Composer (PHP), npm (JavaScript)
- **Testing**: Pest PHP for unit and feature testing
- **Code Quality**: Laravel Pint for code formatting

---

## 🔧 Hardware Requirements

### **EDMER Device (Energy-Driven Monitoring and Evaluation Relay)**

The system requires the proprietary **EDMER** IoT device for power monitoring:

#### **Core Components**
- **Microcontroller**: NodeMCU (ESP8266) with Wi-Fi capability
- **Power Sensor**: PZEM-004T 100A current transformer sensor
- **Control**: Relay module for remote switching
- **Safety**: Integrated fuse protection
- **Connectivity**: Dual receptacle outputs (2-socket capacity)

#### **Technical Specifications**
- **Monitoring Parameters**: Voltage, Current, Power, Energy, Power Factor
- **Measurement Accuracy**: ±1% (PZEM-004T specification)
- **Current Range**: 0-100A AC
- **Voltage Range**: 80-260V AC
- **Communication**: Wi-Fi 802.11 b/g/n
- **Power Supply**: AC mains powered
- **Operating Temperature**: -10°C to +60°C

#### **Installation Requirements**
- **Network**: Stable Wi-Fi connection (2.4GHz)
- **Power**: AC mains connection (220V/110V depending on region)
- **Mounting**: DIN rail or wall-mount compatible
- **Load Capacity**: Maximum 2 appliances per device

---

## 🚀 Installation

### **Prerequisites**

Ensure your system meets these requirements:
- **PHP**: 8.2 or higher
- **Python**: 3.8+ (for AI service)
- **MySQL**: 8.0+
- **Node.js**: 18+ (for frontend assets)
- **Composer**: Latest version
- **Git**: For version control

### **Step 1: Clone Repository**

```bash
git clone https://github.com/courant-energy/courant.git
cd courant
```

### **Step 2: Laravel Application Setup**

```bash
# Install PHP dependencies
composer install

# Copy environment configuration
copy .env.example .env

# Generate application key
php artisan key:generate

# Create storage links
php artisan storage:link
```

### **Step 3: Database Configuration**

1. **Create MySQL Database**:
```sql
CREATE DATABASE courant CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'courant_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON courant.* TO 'courant_user'@'localhost';
FLUSH PRIVILEGES;
```

2. **Update `.env` file**:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=courant
DB_USERNAME=courant_user
DB_PASSWORD=your_secure_password
```

3. **Run Migrations**:
```bash
php artisan migrate --seed
```

### **Step 4: AI Service Setup**

```bash
cd courant_ai_service

# Create Python virtual environment
python -m venv venv

# Activate virtual environment
# Windows:
venv\Scripts\activate
# Linux/MacOS:
source venv/bin/activate

# Install Python dependencies
pip install -r requirements.txt
```

### **Step 5: Frontend Assets**

```bash
# Return to main directory
cd ..

# Install Node.js dependencies
npm install

# Build assets for production
npm run build

# For development with hot reload
npm run dev
```

### **Step 6: Queue Configuration**

```bash
# Configure queue worker (for production)
php artisan queue:work --daemon

# Or use supervisor for process management
# Add to supervisor configuration:
# command=php /path/to/courant/artisan queue:work --sleep=3 --tries=3
```

---

## ⚙️ Configuration

### **Environment Variables**

Key configuration options in `.env`:

```env
# Application
APP_NAME="Courant"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=courant
DB_USERNAME=courant_user
DB_PASSWORD=your_secure_password

# MQTT Configuration
MQTT_HOST=your-mqtt-broker.com
MQTT_PORT=1883
MQTT_CLIENT_ID=courant_app
MQTT_USERNAME=mqtt_user
MQTT_PASSWORD=mqtt_password

# AI Service
AI_SERVICE_URL=http://localhost:5000
AI_SERVICE_TIMEOUT=30
GEMINI_API_KEY=
GEMINI_MODEL_ID=gemini-2.0-flash

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password

# Queue Configuration
QUEUE_CONNECTION=database
```

> ⚠️ Keep `GEMINI_API_KEY` only in environment/secrets management and never commit real key values to the repository, logs, or documentation.

### **MQTT Broker Setup**

For IoT device communication, configure an MQTT broker:

#### **Option 1: Cloud MQTT Service**
- **AWS IoT Core**: Enterprise-grade with device management
- **HiveMQ Cloud**: Developer-friendly with free tier
- **CloudMQTT**: Simple setup with various plans

#### **Option 2: Self-Hosted**
```bash
# Install Mosquitto MQTT Broker
# Ubuntu/Debian:
sudo apt update
sudo apt install mosquitto mosquitto-clients

# Start service
sudo systemctl start mosquitto
sudo systemctl enable mosquitto

# Configure authentication
sudo mosquitto_passwd -c /etc/mosquitto/passwd mqtt_user
```

### **AI Service Configuration**

Update `courant_ai_service/app.py` database connection:

```python
# Database configuration
DB_HOST = 'localhost'
DB_NAME = 'courant'
DB_USER = 'courant_user'
DB_PASS = 'your_secure_password'
```

---

## 📖 Usage Guide

### **Initial Setup**

1. **Access Application**: Navigate to `https://your-domain.com`
2. **Create Admin Account**: Register the first admin user
3. **Configure Settings**: Set company information and preferences

### **Device Registration**

1. **Power on EDMER Device**: Ensure Wi-Fi connection
2. **Navigate to Admin Panel**: `/admin/iot-devices`
3. **Whitelist Device UID**: Add device unique identifier
4. **Configure Profiles**: Set appliance names and thresholds

### **Dashboard Overview**

#### **Real-Time Monitoring**
- **Live Metrics**: View current voltage, current, power readings
- **Historical Charts**: Analyze trends over time periods
- **Device Status**: Monitor connection and health status
- **Quick Actions**: Remote control and emergency shutoff

#### **Alert Management**
- **Active Alerts**: View and acknowledge current issues
- **Alert History**: Track resolved incidents
- **Notification Settings**: Configure delivery preferences
- **Escalation Rules**: Set priority-based routing

### **Reporting Features**

#### **Generate Reports**
1. Navigate to **Reports** section
2. Select report type and date range
3. Apply filters (device, appliance, alert type)
4. Export as CSV or PDF

#### **Available Reports**
- **Energy Consumption**: Usage patterns and cost analysis
- **Equipment Performance**: Efficiency and utilization metrics
- **Anomaly Analysis**: Fault detection summaries
- **Cost Analysis**: ROI and savings calculations

### **Remote Control Operations**

#### **Socket Management**
1. **Navigate to Sockets**: View connected appliances
2. **Toggle Control**: Remote on/off switching
3. **Schedule Operations**: Set automated time-based controls
4. **Load Management**: Balance power distribution

---

## 📡 API Documentation

### **Authentication**

All API endpoints require authentication using Laravel Sanctum tokens:

```bash
# Login and get token
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

# Use token in headers
Authorization: Bearer {token}
```

### **Core Endpoints**

#### **Device Management**
```bash
# Get all devices
GET /api/devices

# Get device details
GET /api/devices/{id}

# Update device settings
PUT /api/devices/{id}
```

#### **Real-Time Data**
```bash
# Get latest readings
GET /api/readings/latest/{device_id}

# Get historical data
GET /api/readings/history/{device_id}?from={date}&to={date}

# Get live power metrics
GET /api/power/current/{device_id}
```

#### **Socket Control**
```bash
# Toggle socket state
POST /api/sockets/{id}/toggle

# Schedule operation
POST /api/sockets/{id}/schedule
{
  "action": "on|off",
  "scheduled_at": "2024-12-01 14:30:00"
}
```

#### **Alerts & Notifications**
```bash
# Get active alerts
GET /api/alerts/active

# Acknowledge alert
POST /api/alerts/{id}/acknowledge

# Get alert history
GET /api/alerts/history?page=1&limit=50
```

### **AI Service Endpoints**

The Flask AI service provides anomaly detection endpoints:

```bash
# Base URL: http://localhost:5000

# Real-time anomaly detection
POST /predict_lstm_anomaly
{
  "device_uid": "device123",
  "profile_id": 1,
  "current_data": {
    "voltage": 220.5,
    "current": 12.3,
    "power": 2710.15,
    "energy": 15.67,
    "power_factor": 0.92
  }
}

# Batch anomaly processing
POST /batch_anomaly_detection
{
  "profile_id": 1,
  "hours_back": 24
}

# Model training
POST /train_models/{profile_id}

# Health check
GET /health
```

---

## 🏗 Architecture

### **System Architecture Overview**

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   EDMER Device  │    │  MQTT Broker    │    │  Laravel App    │
│                 │────│                 │────│                 │
│ • ESP8266       │    │ • Message Queue │    │ • Web Interface │
│ • PZEM Sensor   │    │ • Topic Routing │    │ • API Endpoints │
│ • Relay Control │    │ • Authentication│    │ • Job Processing│
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                                        │
                                                        │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│    MySQL DB     │    │  AI ML Service  │    │   FilamentPHP   │
│                 │────│                 │────│                 │
│ • Time Series   │    │ • Flask API     │    │ • Admin Panel   │
│ • User Data     │    │ • LSTM Models   │    │ • User Interface│
│ • Device Config │    │ • Predictions   │    │ • Real-time UI  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### **Data Flow Architecture**

1. **Data Collection**: EDMER devices collect power metrics via PZEM sensors
2. **IoT Communication**: Data transmitted via MQTT to Laravel application
3. **Real-Time Processing**: Immediate display on dashboard and anomaly checking
4. **AI Analysis**: LSTM models analyze patterns for predictive insights
5. **Alert Generation**: Automated notifications for anomalies and faults
6. **Historical Storage**: Time-series data storage for reporting and analysis

### **Component Interaction**

#### **Laravel Application Layer**
- **Controllers**: Handle HTTP requests and API endpoints
- **Models**: Eloquent ORM for database interactions
- **Jobs**: Background processing for MQTT data and AI analysis
- **Services**: Business logic for anomaly detection and notifications
- **Middleware**: Authentication, rate limiting, and request validation

#### **AI/ML Service Layer**
- **LSTM Models**: Time-series anomaly detection per appliance profile
- **Isolation Forest**: Global outlier detection for batch processing
- **Model Training**: Automated retraining based on new data patterns
- **Prediction API**: Real-time inference for anomaly scoring

#### **Database Schema**
- **Users**: Authentication and role management
- **IoT Devices**: EDMER device registration and configuration
- **Profiles**: Appliance definitions and monitoring thresholds  
- **Readings**: Time-series power consumption data
- **Alerts**: Anomaly notifications and resolution tracking
- **Sockets**: Remote control state and scheduling

---

## 🧪 Testing

### **Running Tests**

The application uses **Pest PHP** for testing:

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/DeviceManagementTest.php
```

### **Test Categories**

#### **Feature Tests**
- **Authentication**: Login, registration, password reset
- **Device Management**: CRUD operations for IoT devices
- **Real-Time Data**: MQTT data processing and display
- **Alert System**: Notification generation and delivery
- **Socket Control**: Remote switching and scheduling
- **Reporting**: PDF/CSV generation and data accuracy

#### **Unit Tests**
- **Models**: Eloquent relationships and data validation
- **Services**: Business logic and anomaly detection algorithms
- **Jobs**: Background processing and queue handling
- **Utilities**: Helper functions and data transformations

#### **AI Service Tests**

```bash
cd courant_ai_service

# Run Python tests
python -m pytest tests/

# Run with coverage
python -m pytest tests/ --cov=app

# Test specific functionality
python -m pytest tests/test_lstm_predictions.py
```

### **Creating New Tests**

```bash
# Create feature test
php artisan make:test DeviceControlTest

# Create unit test
php artisan make:test --unit AnomalyServiceTest

# Test structure example
<?php

uses(Tests\TestCase::class);

test('device can be controlled remotely', function () {
    $device = IoTDevice::factory()->create();
    
    $response = $this->postJson("/api/sockets/{$device->id}/toggle");
    
    $response->assertStatus(200)
             ->assertJson(['status' => 'success']);
});
```

---

## 🚢 Deployment

### **Production Environment**

#### **Server Requirements**
- **OS**: Ubuntu 20.04 LTS or CentOS 8+
- **Web Server**: Nginx or Apache with SSL
- **PHP**: 8.2+ with required extensions
- **Python**: 3.8+ for AI service
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Memory**: Minimum 4GB RAM (8GB+ recommended)
- **Storage**: SSD with 50GB+ available space

#### **Nginx Configuration**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com;
    root /var/www/courant/public;

    index index.php;

    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### **Process Management with Supervisor**

```ini
[program:courant-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/courant/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/courant/storage/logs/worker.log

[program:courant-ai-service]
command=/var/www/courant/courant_ai_service/venv/bin/python /var/www/courant/courant_ai_service/app.py
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/courant/storage/logs/ai-service.log
```

### **Docker Deployment**

#### **Docker Compose Configuration**

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=production
      - DB_HOST=mysql
      - DB_DATABASE=courant
    depends_on:
      - mysql
      - redis
    volumes:
      - ./storage:/var/www/html/storage

  ai-service:
    build:
      context: ./courant_ai_service
      dockerfile: Dockerfile
    ports:
      - "5000:5000"
    depends_on:
      - mysql
    environment:
      - DB_HOST=mysql
      - DB_NAME=courant

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: courant
      MYSQL_USER: courant_user
      MYSQL_PASSWORD: password
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:7-alpine
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data

volumes:
  mysql_data:
  redis_data:
```

### **Deployment Checklist**

- [ ] SSL certificate configured and valid
- [ ] Database backups scheduled
- [ ] Log rotation configured
- [ ] Monitoring and alerting setup
- [ ] Queue workers running and supervised  
- [ ] AI service health monitoring
- [ ] MQTT broker secured and monitored
- [ ] Performance optimization applied
- [ ] Security headers configured
- [ ] Backup and disaster recovery tested

---

## ⚠️ Limitations & Disclaimers

### **System Limitations**

#### **Hardware Constraints**
- **Socket Capacity**: Maximum of 2 appliances per EDMER device
- **Network Dependency**: Requires stable Wi-Fi connection (2.4GHz only)
- **Power Monitoring**: Limited to core electrical parameters (voltage, current, power, energy, power factor)
- **Load Capacity**: PZEM-004T sensor maximum rating of 100A AC

#### **Software Limitations**
- **Real-Time Processing**: AI analysis may have 1-3 second delays
- **Historical Data**: LSTM predictions require minimum 30 days of historical data for optimal accuracy
- **Concurrent Users**: Performance tested for up to 100 simultaneous users
- **Data Retention**: Default 2-year data retention policy (configurable)

#### **Network Requirements**
- **Internet Connectivity**: Required for cloud MQTT brokers and external notifications
- **Bandwidth**: Minimum 1 Mbps upload speed per 10 devices
- **Latency**: MQTT broker latency should be <100ms for optimal real-time performance

### **Important Disclaimers**

#### **🚨 Safety & Liability**

> **CRITICAL NOTICE**: This system provides **monitoring and advisory capabilities only**. It does not automatically prevent electrical faults or equipment failures.

- **Alerts are Suggestive, Not Corrective**: The system identifies potential issues but does not automatically implement fixes
- **User Responsibility**: All corrective actions, maintenance decisions, and safety measures remain the sole responsibility of the MSME operator
- **Professional Consultation**: For significant electrical anomalies, always consult qualified electrical professionals
- **Insurance & Liability**: This system does not replace professional electrical inspections or insurance requirements

#### **🔒 Data & Privacy**

- **Data Collection**: System collects and stores power consumption patterns and usage data
- **Local Processing**: Core AI processing occurs locally; external data transmission is minimized
- **User Consent**: Continued use implies consent to data collection as outlined in privacy policy
- **Data Retention**: Historical data stored according to configured retention policies

#### **⚡ Electrical Safety**

- **Professional Installation**: EDMER device installation should be performed by qualified electricians
- **Electrical Codes**: Installation must comply with local electrical codes and regulations
- **Load Ratings**: Do not exceed EDMER device or sensor specifications
- **Emergency Procedures**: System alerts do not replace standard electrical safety protocols

#### **🛡️ System Reliability**

- **Best Effort Service**: While designed for high availability, 100% uptime cannot be guaranteed
- **Backup Systems**: Critical operations should not rely solely on this monitoring system
- **Regular Maintenance**: Periodic system updates and hardware maintenance required
- **Environmental Factors**: Performance may be affected by extreme environmental conditions

### **Recommended Best Practices**

1. **Regular Professional Inspections**: Supplement system monitoring with periodic electrical audits
2. **Redundant Safety Measures**: Maintain traditional circuit protection and safety devices
3. **Staff Training**: Ensure operators understand system capabilities and limitations
4. **Emergency Protocols**: Develop response procedures that don't depend on system availability
5. **Data Backups**: Implement regular backups of configuration and historical data

---

## 🤝 Contributing

We welcome contributions from the community! Please follow these guidelines:

### **Development Setup**

```bash
# Fork the repository
git clone https://github.com/yourusername/courant.git
cd courant

# Create feature branch
git checkout -b feature/your-feature-name

# Install development dependencies
composer install --dev
npm install

# Run development environment
php artisan serve
npm run dev
```

### **Contribution Guidelines**

1. **Code Style**: Follow PSR-12 standards for PHP, use Laravel Pint
2. **Testing**: Add tests for new features and maintain coverage
3. **Documentation**: Update README and inline documentation
4. **Commit Messages**: Use conventional commit format
5. **Pull Requests**: Provide detailed description of changes

### **Areas for Contribution**

- **IoT Device Support**: Additional sensor types and manufacturers
- **ML Algorithms**: Alternative anomaly detection approaches
- **UI/UX Improvements**: Enhanced dashboard and mobile experience
- **Integration**: Third-party service connectors
- **Documentation**: Tutorials and deployment guides
- **Testing**: Expanded test coverage and automated testing

---

## 📞 Support

### **Documentation**
- **Installation Guide**: Detailed setup instructions above
- **API Documentation**: Complete endpoint reference available
- **Troubleshooting**: Common issues and solutions

### **Community Support**
- **GitHub Issues**: [Report bugs and feature requests](https://github.com/courant-energy/courant/issues)
- **Discussions**: [Community Q&A and feature discussions](https://github.com/courant-energy/courant/discussions)

### **Professional Support**
- **Enterprise Support**: Available for production deployments
- **Custom Development**: Tailored solutions and integrations
- **Training Services**: On-site training and workshops

### **Contact Information**
- **Email**: support@courant-energy.com
- **Website**: https://courant-energy.com
- **Documentation**: https://docs.courant-energy.com

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

### **Third-Party Licenses**
- **Laravel Framework**: MIT License
- **FilamentPHP**: MIT License  
- **TensorFlow**: Apache 2.0 License
- **Flask**: BSD-3-Clause License

---

<div align="center">

**Built with ❤️ for the MSME Community**

*Empowering businesses through intelligent power management*

[![GitHub stars](https://img.shields.io/github/stars/courant-energy/courant?style=social)](https://github.com/courant-energy/courant/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/courant-energy/courant?style=social)](https://github.com/courant-energy/courant/network)

</div>
