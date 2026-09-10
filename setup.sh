#!/bin/bash

echo "🚀 CDM Career Recommendation System - Setup Script"
echo "=================================================="

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check Node.js
echo -e "\n${YELLOW}Checking Node.js installation...${NC}"
if ! command -v node &> /dev/null; then
    echo -e "${RED}Node.js is not installed. Please install Node.js 16+${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Node.js $(node -v) installed${NC}"

# Check PHP
echo -e "\n${YELLOW}Checking PHP installation...${NC}"
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Please install PHP 8.1+${NC}"
    exit 1
fi
echo -e "${GREEN}✓ PHP $(php -v | head -n 1) installed${NC}"

# Check Composer
echo -e "\n${YELLOW}Checking Composer installation...${NC}"
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Composer is not installed. Please install Composer${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Composer installed${NC}"

# Backend Setup
echo -e "\n${YELLOW}Setting up Backend...${NC}"
cd backend || exit

if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ Created .env file${NC}"
fi

composer install
echo -e "${GREEN}✓ Installed PHP dependencies${NC}"

php artisan key:generate
echo -e "${GREEN}✓ Generated APP_KEY${NC}"

php artisan storage:link 2>/dev/null || true
echo -e "${GREEN}✓ Created storage symlink${NC}"

cd ..

# Frontend Setup
echo -e "\n${YELLOW}Setting up Frontend...${NC}"
cd frontend || exit

if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ Created .env file${NC}"
fi

npm install
echo -e "${GREEN}✓ Installed npm dependencies${NC}"

cd ..

# Summary
echo -e "\n${GREEN}=================================================="
echo "✓ Setup completed successfully!"
echo "==================================================${NC}"

echo -e "\n${YELLOW}Next steps:${NC}"
echo "1. Update database credentials in backend/.env"
echo "2. Run: cd backend && php artisan migrate"
echo "3. Run: cd backend && php artisan db:seed"
echo "4. Start backend: cd backend && php artisan serve"
echo "5. Start frontend: cd frontend && npm run dev"

echo -e "\n${YELLOW}URLs:${NC}"
echo "- Backend:  http://localhost:8000"
echo "- Frontend: http://localhost:5173"

echo ""
