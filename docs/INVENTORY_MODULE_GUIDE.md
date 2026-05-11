# Inventory Module — User Guide

PRMS v3 includes a full **Inventory and Asset Management System (IMS)** that tracks stock from receipt through to disposal. This guide explains each sub-module and how staff use them day-to-day.

---

## Table of Contents

1. [Overview](#overview)
2. [Module Structure](#module-structure)
3. [Setup (First Time)](#setup-first-time)
4. [Roles & Permissions](#roles--permissions)
5. [Items & Catalogue](#items--catalogue)
6. [Locations](#locations)
7. [Receiving Goods (GRN)](#receiving-goods-grn)
8. [Issuing Stock](#issuing-stock)
9. [Stock Transfers](#stock-transfers)
10. [Stock Adjustments](#stock-adjustments)
11. [Stock Count (Stocktake)](#stock-count-stocktake)
12. [Returns](#returns)
13. [Disposals](#disposals)
14. [Incidents & Losses](#incidents--losses)
15. [Quarantine & Recall](#quarantine--recall)
16. [Write-Downs](#write-downs)
17. [Reports](#reports)
18. [Dashboard KPIs](#dashboard-kpis)
19. [Integration with Procurement](#integration-with-procurement)

---

## Overview

The IMS provides end-to-end stock control aligned with the **GOJ (Government of Jamaica) Financial Administration and Audit Act** and IPSAS standards for inventory accounting.

Key principles:
- **Segregation of Duties** — different users receive, issue, adjust and approve stock.
- **FEFO / FIFO** — stock is consumed in expiry-date first, then receipt-date order.
- **Immutable audit trail** — every stock movement creates a permanent transaction record.
- **Reorder alerts** — items automatically flag when stock falls to or below the reorder level.

---

## Module Structure

```
/inventory/
├── dashboard.php           ← KPI summary
├── items/                  ← Item catalogue management
├── locations/              ← Warehouse / store locations
├── receiving/              ← Goods Received Notes (GRN)
├── issuing/                ← Stock issue vouchers
├── transfers/              ← Inter-location transfers
├── adjustments/            ← Stock adjustments (gains/losses)
├── stocktake/              ← Periodic stock counts
├── returns/                ← Returns from departments
├── disposal/               ← Condemned / surplus disposal
├── incidents/              ← Loss, theft, damage incidents
├── quarantine/             ← Items under investigation
├── recall/                 ← Supplier recalls
├── writedowns/             ← Asset write-downs
└── reports/                ← Standard reports
```

---

## Setup (First Time)

Before using the inventory module:

1. **Run the migration** (Admin / DBA):
   ```bash
   mysql -u prms_user -p prms_ims < migrations/019_inventory_management_system.sql
   mysql -u prms_user -p prms_ims < migrations/019b_inventory_schema_alignment.sql
   mysql -u prms_user -p prms_ims < migrations/019c_goj_compliance.sql
   mysql -u prms_user -p prms_ims < migrations/020_procurement_inventory_link.sql
   ```

2. **Assign inventory roles** to users via the Admin panel
   (roles: Store Keeper, Store Manager, Inventory Auditor, etc.)

3. **Create Locations** (`/inventory/locations/add.php`) — at least one store/warehouse.

4. **Add Items** (`/inventory/items/add.php`) — build the item catalogue.

---

## Roles & Permissions

| Role | Typical Capabilities |
|------|---------------------|
| **Store Keeper** | Receive goods, issue stock, record returns |
| **Store Manager** | Approve transfers, adjustments, disposals |
| **Inventory Auditor** | Read-only access; run reports; conduct stocktakes |
| **Admin / SuperAdmin** | Full access including delete |

Permissions are controlled via the same RBAC system used by procurement.

---

## Items & Catalogue

**Path:** `/inventory/items/`

Each item record holds:
- Item code (auto-generated, e.g. `ITM-00001`)
- Item name, description
- Category (Consumables, Lab Supplies, IT Consumables, etc.)
- Unit of measure
- Criticality class (Critical, Essential, Routine, Obsolete)
- Reorder level and reorder quantity
- Average cost (auto-updated on every receipt)

**To add an item:**
1. Go to **Inventory → Items → Add Item**
2. Fill in item name, category, UOM, reorder level
3. Assign risk classes if applicable (High-Value, Controlled, Hazardous, etc.)
4. Save — the system generates a unique item code.

---

## Locations

**Path:** `/inventory/locations/`

Locations represent physical storage points (rooms, storerooms, cabinets, off-site). Each location has:
- Location code (e.g. `MAIN-STORE`)
- Site name / description
- Type (Main Store, Satellite Store, Lab, etc.)

Stock is tracked **per item per location**.

---

## Receiving Goods (GRN)

**Path:** `/inventory/receiving/`

A **Goods Received Note (GRN)** records incoming stock.

### Manual GRN
1. Go to **Inventory → Receiving → New GRN**
2. Enter PO number (optional), supplier name, delivery note
3. Add items, quantities, lot/batch numbers, expiry dates, unit costs
4. Set status to **RECEIVED** to trigger stock update, or save as **DRAFT** to complete later.

### GRN from a Purchase Order
See [Integration with Procurement](#integration-with-procurement).

### GRN Statuses

| Status | Meaning |
|--------|---------|
| DRAFT | Not yet confirmed; stock not updated |
| INSPECTION | Goods received, awaiting quality check |
| RECEIVED | Fully accepted; stock levels updated |
| COMPLETED | Closed; all paperwork done |
| CANCELLED | Voided |

---

## Issuing Stock

**Path:** `/inventory/issuing/`

Stock issues record items leaving the store for departmental use.

1. Go to **Inventory → Issuing → Issue Stock**
2. Select the requesting department/requisition
3. Add items and quantities to issue
4. System uses **FEFO** to pick the correct stock batch
5. Confirm — stock levels are immediately decremented

---

## Stock Transfers

**Path:** `/inventory/transfers/`

Transfers move stock between locations without creating a procurement event.

- **From Location** → **To Location**
- Requires approval if the transfer value exceeds a threshold
- Creates matching TRANSFER_OUT and TRANSFER_IN transaction records

---

## Stock Adjustments

**Path:** `/inventory/adjustments/`

Adjustments correct stock levels (discovered variance, spoilage, damage).

| Adjustment Type | Effect |
|-----------------|--------|
| Increase (found stock) | Adds quantity |
| Decrease (write-off)   | Removes quantity |

All adjustments require **manager approval** before stock is updated.

---

## Stock Count (Stocktake)

**Path:** `/inventory/stocktake/`

Periodic physical counts to verify system stock matches actual stock.

1. **Create Count Sheet** — select location and items
2. **Print count sheet** (optional) and physically count
3. **Enter counted quantities** on-screen
4. System highlights **variances**
5. Approve — variances are posted as adjustment transactions

---

## Returns

**Path:** `/inventory/returns/`

Records stock returned from departments back to the store (unused items, cancelled orders).

---

## Disposals

**Path:** `/inventory/disposal/`

Manages the formal disposal of surplus, damaged, or obsolete items.

1. Create a **Disposal Form** listing items and reason
2. Obtain manager and/or board approval
3. Record disposal method (destruction, auction, donation)
4. System removes items from stock and creates a DISPOSAL transaction

---

## Incidents & Losses

**Path:** `/inventory/incidents/`

Records unplanned stock losses (theft, breakage, contamination). Linked to the audit trail.

---

## Quarantine & Recall

**Path:** `/inventory/quarantine/` and `/inventory/recall/`

- **Quarantine:** holds items under investigation (suspected contamination, awaiting test results)
- **Recall:** manages supplier recalls, including tracing affected batches and notifying stakeholders

---

## Write-Downs

**Path:** `/inventory/writedowns/`

Records a reduction in the carrying value of stock without physically removing units (e.g. obsolete items held at lower-of-cost-or-net-realisable-value per IPSAS 12).

---

## Reports

**Path:** `/inventory/reports/`

| Report | Description |
|--------|-------------|
| Stock Valuation | Current stock value by location and category |
| Transaction History | Full audit trail of all stock movements |
| Reorder Report | Items at or below reorder level |
| Expiry Report | Items expiring within a user-defined period |
| Audit Exceptions | Adjustments, incidents, disposals requiring review |

All reports can be exported to PDF.

---

## Dashboard KPIs

The Inventory Dashboard (`/inventory/dashboard.php`) shows:

| KPI | Description |
|-----|-------------|
| Active Items | Count of active catalogue items |
| Total Inventory Value | Sum of (qty × unit cost) across all usable stock |
| Low Stock Alerts | Items at or below reorder level |
| Expiring (90 days) | Items with expiry date within 90 days |
| Pending Requisitions | Submitted requisitions awaiting approval |
| Pending GRNs | GRNs in DRAFT or INSPECTION status |
| Pending Transfers | Transfers awaiting approval |
| Pending Adjustments | Adjustments awaiting approval |
| Pending Disposals | Disposals awaiting approval |

---

## Integration with Procurement

When goods covered by a Purchase Order arrive, staff can create a GRN directly from the PO:

1. Open the PO: **Procurement → Purchase Orders → View PO**
2. Click **Receive to Inventory**
3. A Draft GRN is created pre-filled with the PO number, supplier name, and line items
4. Complete the GRN (quantities, batches, locations) and set to **RECEIVED**

The GRN appears in the **Inventory — Goods Received Notes** panel on the PO view.

See the [Procurement–Inventory Integration Guide](PROCUREMENT_INVENTORY_INTEGRATION.md) for full details.
