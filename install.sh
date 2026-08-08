#!/bin/bash

# =====================================================
# Smart Resume Analyzer - Installation Script
# =====================================================
# This script automates the setup process for the
# Smart Resume Analyzer application.

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo -e "${BLUE}======================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}======================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Check for required commands
check_requirements() {
    print_header "Checking Requirements"

    # Check PHP
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed. Please install PHP 8.0 or higher."
        exit 1
    fi
    PHP_VERSION=$(php -v | grep -oP 'PHP \K[0-9.]+' | head -1)
    print_success "PHP $PHP_VERSION found"

    # Check Composer
    if ! command -v composer &> /dev/null; then
        print_error "Composer is not installed. Please install Composer first."
        exit 1
    fi
    print_success "Composer found"

    # Check MySQL
    if ! command -v mysql &> /dev/null; then
        print_warning "MySQL client not found. Database setup may require manual configuration."
    else
        print_success "MySQL client found"
    fi

    echo ""
}

# Create necessary directories
create_directories() {
    print_header "Creating Directories"

    mkdir -p uploads logs database config

    print_success "Created uploads directory"
    print_success "Created logs directory"
    print_success "Created database directory"

    # Set proper permissions
    chmod -R 755 uploads logs

    echo ""
}

# Install PHP dependencies
install_dependencies() {
    print_header "Installing PHP Dependencies"

    if [ -f "composer.json" ]; then
        composer install --no-dev --optimize-autoloader
        print_success "PHP dependencies installed"
    else
        print_error "composer.json not found"
        exit 1
    fi

    echo ""
}

# Setup environment configuration
setup_env() {
    print_header "Setting Up Environment"

    if [ ! -f ".env" ]; then
        if [ -f ".env.example" ]; then
            cp .env.example .env
            print_success "Created .env from .env.example"
        else
            print_error ".env.example not found"
            exit 1
        fi
    else
        print_warning ".env file already exists. Skipping..."
    fi

    # Make .env file readable only by owner
    chmod 600 .env

    echo ""
}

# Database setup
setup_database() {
    print_header "Database Setup"

    # Check if .env file exists and read database credentials
    if [ ! -f ".env" ]; then
        print_error ".env file not found"
        return 1
    fi

    # Source .env file (simplified - doesn't handle all cases)
    export $(cat .env | grep -v '#' | xargs)

    echo ""
    echo "Enter your MySQL credentials for database setup:"
    echo ""

    read -p "MySQL Host (default: localhost): " db_host
    db_host=${db_host:-localhost}

    read -p "MySQL Port (default: 3306): " db_port
    db_port=${db_port:-3306}

    read -p "MySQL User (default: root): " db_user
    db_user=${db_user:-root}

    read -sp "MySQL Password: " db_password
    echo ""

    read -p "Database Name (default: resume_analyzer): " db_name
    db_name=${db_name:-resume_analyzer}

    echo ""

    # Test connection
    if mysql -h "$db_host" -P "$db_port" -u "$db_user" -p"$db_password" -e "SELECT 1" > /dev/null 2>&1; then
        print_success "MySQL connection successful"

        # Create database if it doesn't exist
        mysql -h "$db_host" -P "$db_port" -u "$db_user" -p"$db_password" -e \
            "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        print_success "Database created/verified"

        # Import schema
        if [ -f "database/schema.sql" ]; then
            mysql -h "$db_host" -P "$db_port" -u "$db_user" -p"$db_password" "$db_name" < database/schema.sql
            print_success "Database schema imported"
        else
            print_error "database/schema.sql not found"
        fi

        # Update .env with database credentials
        sed -i.bak "s/DB_HOST=.*/DB_HOST=\"$db_host\"/" .env
        sed -i.bak "s/DB_PORT=.*/DB_PORT=\"$db_port\"/" .env
        sed -i.bak "s/DB_USER=.*/DB_USER=\"$db_user\"/" .env
        sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=\"$db_password\"/" .env
        sed -i.bak "s/DB_NAME=.*/DB_NAME=\"$db_name\"/" .env

        print_success ".env file updated with database credentials"

    else
        print_error "MySQL connection failed. Please verify your credentials."
        print_warning "You can manually import the database schema using:"
        echo "  mysql -u $db_user -p $db_name < database/schema.sql"
    fi

    echo ""
}

# Verify installation
verify_installation() {
    print_header "Verifying Installation"

    # Check key files exist
    if [ -f ".env" ]; then
        print_success ".env file exists"
    else
        print_error ".env file not found"
    fi

    if [ -d "vendor" ]; then
        print_success "vendor directory exists"
    else
        print_error "vendor directory not found"
    fi

    if [ -d "uploads" ]; then
        print_success "uploads directory exists"
    else
        print_error "uploads directory not found"
    fi

    if [ -d "logs" ]; then
        print_success "logs directory exists"
    else
        print_error "logs directory not found"
    fi

    echo ""
}

# Show next steps
show_next_steps() {
    print_header "Installation Complete!"

    echo -e "${GREEN}Smart Resume Analyzer has been successfully installed!${NC}"
    echo ""
    echo "Next steps:"
    echo ""
    echo "1. Configure your .env file:"
    echo "   nano .env"
    echo ""
    echo "2. Set your API keys (optional but recommended):"
    echo "   - HuggingFace API key for NLP"
    echo "   - OpenAI API key (alternative to HuggingFace)"
    echo ""
    echo "3. Start the development server:"
    echo "   php -S localhost:8000"
    echo ""
    echo "4. Open your browser and visit:"
    echo "   http://localhost:8000"
    echo ""
    echo "5. Register an account or login with demo credentials:"
    echo "   Email: demo@example.com"
    echo "   Password: Demo@12345"
    echo ""
    echo "Documentation: See README.md for detailed information"
    echo ""
}

# Main installation flow
main() {
    print_header "Smart Resume Analyzer Setup"
    echo ""

    # Check if running from correct directory
    if [ ! -f "composer.json" ] || [ ! -f ".env.example" ]; then
        print_error "Please run this script from the project root directory"
        exit 1
    fi

    # Run installation steps
    check_requirements
    create_directories
    install_dependencies
    setup_env

    read -p "Would you like to setup the database now? (y/n): " setup_db
    if [ "$setup_db" = "y" ] || [ "$setup_db" = "Y" ]; then
        setup_database
    else
        print_warning "You can setup the database later by running: mysql -u root -p < database/schema.sql"
    fi

    verify_installation
    show_next_steps
}

# Run main function
main
