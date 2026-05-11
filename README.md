# PRMS v3 — DGC Procurement & Inventory Management System

A web-based system for the Department of Government Chemistry (DGC), Jamaica, that manages the full procurement lifecycle and integrates with an inventory management module.

---

## Features

### Procurement
- Procurement request workflow (submit → HOD → Finance → Director → PPC/Cabinet)
- RFQ (Request for Quotation) generation and vendor management
- Purchase Order creation, approval, and variations
- Commitment tracking (GFMS integration)
- Invoice recording and payment tracking
- Reimbursement and petty cash workflows
- Document uploads (signed requests, PO files)

### Inventory (IMS)
- Item catalogue with categories, criticality classes, and risk classes
- Multi-location stock tracking with FEFO/FIFO consumption
- Goods Received Notes (GRN) — including auto-fill from Purchase Orders
- Stock issuing, transfers, adjustments, returns
- Periodic stocktake (physical count)
- Disposal, write-down, quarantine, recall, and incident management
- Reorder alerts and expiry alerts
- Standard reports (Stock Valuation, Transaction History, Reorder, Expiry, Audit Exceptions)

### System
- Role-Based Access Control (12 configurable roles)
- Acting-role assignments
- Full audit trail on all tables
- Email notifications (PHPMailer / SMTP)
- PDF generation (dompdf)

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.1+ |
| Database | MySQL 8 / MariaDB 10.5+ |
| Frontend | Bootstrap 5.3, Bootstrap Icons |
| PDF | dompdf 3.x |
| Email | PHPMailer 7.x |
| Dependencies | Composer 2.x |

---

## Quick Start (Linux Server)

```bash
# 1. Clone
git clone https://github.com/dsitservicesja-lab/PRMSv3.git /var/www/prms/public
cd /var/www/prms/public

# 2. Install server software
sudo bash deploy/install.sh

# 3. Configure environment
cp .env.example .env
nano .env   # set DB credentials, SMTP, APP_URL

# 4. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 5. Create database and import schema
sudo bash deploy/deploy.sh --init-db
mysql -u prms_user -p prms_ims < prmsv2.sql

# 6. Run migrations
sudo bash deploy/deploy.sh --run-migrations

# 7. Configure your web server
#    Apache:  sudo cp deploy/apache.conf /etc/apache2/sites-available/prms.conf
#    Nginx:   sudo cp deploy/nginx.conf /etc/nginx/sites-available/prms
```

See [docs/LINUX_DEPLOYMENT_GUIDE.md](docs/LINUX_DEPLOYMENT_GUIDE.md) for the full guide.

---

## Documentation

| Document | Description |
|----------|-------------|
| [docs/LINUX_DEPLOYMENT_GUIDE.md](docs/LINUX_DEPLOYMENT_GUIDE.md) | Complete Linux server setup and deployment guide |
| [docs/INVENTORY_MODULE_GUIDE.md](docs/INVENTORY_MODULE_GUIDE.md) | Inventory module user guide |
| [docs/PROCUREMENT_INVENTORY_INTEGRATION.md](docs/PROCUREMENT_INVENTORY_INTEGRATION.md) | How procurement and inventory interact |
| [docs/Procurement-SOP.md](docs/Procurement-SOP.md) | Standard Operating Procedure |
| [docs/WORKFLOW_DIAGRAMS.md](docs/WORKFLOW_DIAGRAMS.md) | Approval workflow diagrams |
| [docs/PERMISSION_SYSTEM_SUMMARY.md](docs/PERMISSION_SYSTEM_SUMMARY.md) | RBAC permission system |

---

## Project Structure

```
/
├── auth/            Login, logout, password management
├── config/          Database, app settings, guards, helpers
├── dashboard/       Role-specific dashboards
├── procurement/     Procurement requests
├── rfq/             Request for Quotation workflow
├── po/              Purchase Orders
├── commitments/     Commitment / GFMS tracking
├── invoice/         Invoice management
├── payment/         Payments
├── reimbursement/   Reimbursement requests
├── petty_cash/      Petty cash float
├── inventory/       Inventory Management System (full module)
├── services/        Business logic services (PHP classes)
├── includes/        Shared header, footer, sidebar
├── assets/          CSS, JS
├── migrations/      SQL migration files (run in order)
├── deploy/          Server configuration and deployment scripts
└── docs/            Documentation
```

---

## Contributing

1. Branch from `application`
2. Follow existing file and naming conventions
3. Add migrations for schema changes (next number in sequence)
4. Test locally against a copy of the database
5. Open a pull request targeting `application`
