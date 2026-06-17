#!/bin/bash
# Validation script for Manager Agent deployment

set -e

echo "🔍 Manager Agent - Deployment Validation"
echo "========================================"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

ERRORS=0
WARNINGS=0

echo ""
echo -e "${GREEN}Checking configuration files...${NC}"

# Check required files
FILES=(
    "Dockerfile"
    "docker-compose.yml"
    "render.yaml"
    "docker/entrypoint.sh"
    "docker/nginx.conf"
    "docker/php-fpm.conf"
    "docker/supervisord.conf"
    "bootstrap/config/database.php"
    ".env.example"
    ".env.production"
    "DEPLOYMENT.md"
)

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${RED}✗${NC} Missing: $file"
        ERRORS=$((ERRORS + 1))
    fi
done

echo ""
echo -e "${GREEN}Checking database configuration...${NC}"

# Check for MySQL in config
if grep -q "mysql" bootstrap/config/database.php; then
    echo -e "${GREEN}✓${NC} MySQL configuration found"
else
    echo -e "${RED}✗${NC} MySQL not found in database.php"
    ERRORS=$((ERRORS + 1))
fi

# Check default connection
if grep -q "'default' => env('DB_CONNECTION', 'mysql')" bootstrap/config/database.php; then
    echo -e "${GREEN}✓${NC} MySQL set as default connection"
else
    echo -e "${YELLOW}⚠${NC} MySQL might not be default connection"
    WARNINGS=$((WARNINGS + 1))
fi

echo ""
echo -e "${GREEN}Checking environment files...${NC}"

# Check .env.example
if grep -q "DB_CONNECTION=mysql" .env.example; then
    echo -e "${GREEN}✓${NC} .env.example configured for MySQL"
else
    echo -e "${RED}✗${NC} .env.example not configured for MySQL"
    ERRORS=$((ERRORS + 1))
fi

# Check .env (local)
if grep -q "DB_CONNECTION=mysql" .env; then
    echo -e "${GREEN}✓${NC} .env configured for MySQL"
else
    echo -e "${YELLOW}⚠${NC} .env might still use PostgreSQL"
    WARNINGS=$((WARNINGS + 1))
fi

echo ""
echo -e "${GREEN}Checking Docker configuration...${NC}"

# Check Dockerfile for MySQL support
if grep -q "pdo_mysql" Dockerfile; then
    echo -e "${GREEN}✓${NC} MySQL PHP extension in Dockerfile"
else
    echo -e "${RED}✗${NC} Missing pdo_mysql in Dockerfile"
    ERRORS=$((ERRORS + 1))
fi

# Check entrypoint script
if [ -x "docker/entrypoint.sh" ]; then
    echo -e "${GREEN}✓${NC} Entrypoint script is executable"
else
    echo -e "${YELLOW}⚠${NC} Entrypoint script might not be executable"
    WARNINGS=$((WARNINGS + 1))
fi

echo ""
echo -e "${GREEN}Checking migrations...${NC}"

# Check for common MySQL-specific functions
MYSQL_PATTERNS=("YEAR(" "MONTH(" "DATE(" "DATEDIFF(" "DATE_FORMAT(" "FROM_UNIXTIME(")
FOUND_MYSQL=0

for pattern in "${MYSQL_PATTERNS[@]}"; do
    if grep -r "$pattern" database/migrations/ 2>/dev/null; then
        echo -e "${YELLOW}⚠${NC} Possible MySQL-specific function found: $pattern"
        FOUND_MYSQL=$((FOUND_MYSQL + 1))
    fi
done

if [ $FOUND_MYSQL -eq 0 ]; then
    echo -e "${GREEN}✓${NC} No MySQL-specific functions detected in migrations"
else
    echo -e "${YELLOW}⚠${NC} $FOUND_MYSQL potential MySQL functions found (may need review)"
    WARNINGS=$((WARNINGS + 1))
fi

echo ""
echo -e "${GREEN}Checking application code...${NC}"

# Look for MySQL connection strings in code
if grep -r "mysql://" app/ routes/ 2>/dev/null | grep -v node_modules; then
    echo -e "${YELLOW}⚠${NC} MySQL connection strings found in code"
    WARNINGS=$((WARNINGS + 1))
else
    echo -e "${GREEN}✓${NC} No hardcoded MySQL connection strings"
fi

echo ""
echo -e "${GREEN}Summary:${NC}"
echo "========================================"
echo -e "Errors: ${RED}$ERRORS${NC}"
echo -e "Warnings: ${YELLOW}$WARNINGS${NC}"

if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✅ All critical checks passed!${NC}"
    
    if [ $WARNINGS -gt 0 ]; then
        echo -e "${YELLOW}⚠️  Review $WARNINGS warnings above${NC}"
    fi
    
    echo ""
    echo -e "${GREEN}Ready for deployment!${NC}"
    echo ""
    echo "Next steps:"
    echo "1. docker-compose up -d  (test locally)"
    echo "2. Verify http://localhost:8080/health returns 200"
    echo "3. Test migrations: docker-compose exec app php artisan migrate:status"
    echo "4. Push to GitHub and deploy to Render"
    
    exit 0
else
    echo -e "${RED}❌ Fix errors before deployment${NC}"
    exit 1
fi
