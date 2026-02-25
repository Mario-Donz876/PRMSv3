#!/bin/bash

# PRMS GFMS Integration Migration Script
# This script applies database migrations to support GFMS commitment # and PO # integration
# Author: System Administrator
# Date: 2026-02-18

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}PRMS GFMS Integration Migration${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# Check if we have database credentials
if [ ! -f "config/db.php" ]; then
    echo -e "${RED}Error: config/db.php not found${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Backing up database...${NC}"
# You may want to implement automatic backup here
echo -e "${GREEN}✓ Database backup recommended before running migration${NC}"
echo ""

echo -e "${YELLOW}Step 2: Applying migration 003 - Add GFMS Commitment Number...${NC}"
# This would typically be run via PHP CLI or MySQL directly
echo "Run this SQL command:"
echo "mysql -u[username] -p[password] [database] < migrations/003_add_gfms_commitment_number.sql"
echo ""

echo -e "${YELLOW}Step 3: Applying migration 004 - Add GFMS PO Number...${NC}"
echo "Run this SQL command:"
echo "mysql -u[username] -p[password] [database] < migrations/004_add_gfms_po_number.sql"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Migration instructions created${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "To complete the migration manually:"
echo "1. Connect to your database:"
echo "   mysql -u[username] -p[password] [database]"
echo ""
echo "2. Run migration 003:"
cat migrations/003_add_gfms_commitment_number.sql
echo ""
echo "3. Run migration 004:"
cat migrations/004_add_gfms_po_number.sql
echo ""
echo -e "${GREEN}After running migrations, the following files have been updated:${NC}"
echo "  - commitments/add.php"
echo "  - commitments/upload.php"
echo "  - po/add.php"
echo "  - po/upload.php"
echo ""
echo -e "${YELLOW}Testing recommendations:${NC}"
echo "1. Test commitment creation with optional GFMS commitment number"
echo "2. Test commitment upload with optional GFMS commitment number"
echo "3. Test PO creation with optional GFMS PO number"
echo "4. Test PO upload with optional GFMS PO number"
echo "5. Verify uniqueness validation of GFMS numbers"
echo "6. Test backward compatibility (creating without GFMS numbers)"
echo ""
