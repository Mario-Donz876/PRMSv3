# Procurement–Inventory Integration Guide

This guide describes how the **Procurement** and **Inventory** modules of PRMS v3 interact, the data flows between them, and how to use the integration features day-to-day.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Data Flow Diagrams](#data-flow-diagrams)
3. [Creating a GRN from a Purchase Order](#creating-a-grn-from-a-purchase-order)
4. [Escalating an Inventory Requisition to Procurement](#escalating-an-inventory-requisition-to-procurement)
5. [Stock Level Awareness in Procurement](#stock-level-awareness-in-procurement)
6. [Database Links](#database-links)
7. [Service Layer Reference](#service-layer-reference)

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         PRMS v3                                 │
│                                                                 │
│  ┌─────────────────────┐       ┌─────────────────────────────┐ │
│  │   PROCUREMENT        │       │        INVENTORY            │ │
│  │                      │       │                             │ │
│  │  procurement_requests│──────▶│  inv_procurement_escalations│ │
│  │  purchase_orders     │──────▶│  inv_goods_received         │ │
│  │  po_items            │       │  inv_grn_items              │ │
│  │  vendors             │       │  inv_items                  │ │
│  └─────────────────────┘       │  inv_stock                  │ │
│            ▲                   │  inv_transactions           │ │
│            │                   └─────────────────────────────┘ │
│  ┌─────────────────────┐                                        │
│  │  ProcurementInventory│                                        │
│  │  Bridge (service)   │                                        │
│  └─────────────────────┘                                        │
└─────────────────────────────────────────────────────────────────┘
```

The `ProcurementInventoryBridge` service (`services/ProcurementInventoryBridge.php`) is the single point of integration logic. Direct queries between modules should go through this class.

---

## Data Flow Diagrams

### Flow 1 — Purchase Order → Goods Receipt

```
Vendor delivers goods
        │
        ▼
Staff opens PO in PRMS
        │
        ▼
Click "Receive to Inventory"   (/po/receive_to_inventory.php)
        │
        ▼
System creates Draft GRN       (inv_goods_received + inv_grn_items)
  pre-filled from PO items
        │
        ▼
Store keeper completes GRN     (fills qty received, batches, expiry)
  sets status → RECEIVED
        │
        ▼
Stock levels updated            (inv_stock, inv_transactions)
        │
        ▼
GRN visible on PO view page    (linked via procurement_po_id FK)
```

### Flow 2 — Inventory Shortage → Procurement Request

```
Store detects low stock / unfulfillable requisition
        │
        ▼
Inventory officer reviews requisition
        │
        ▼
Clicks "Escalate to Procurement"
        │
        ▼
ProcurementInventoryBridge::escalateRequisition()
  creates Draft procurement_request
  links via inv_procurement_escalations
        │
        ▼
Procurement officer processes the request normally
  (approval chain, RFQ, PO, etc.)
```

---

## Creating a GRN from a Purchase Order

### Step-by-step

1. Navigate to **Procurement → Purchase Orders** and open the relevant PO.
2. In the **Actions** panel on the right, click **Receive to Inventory**.
   - *This button is only visible if the inventory module is set up and you have the `receive_goods` permission.*
3. The system:
   - Validates the PO exists and the inventory module is ready
   - Checks for an existing Draft GRN (redirects to it if found)
   - Creates a new Draft GRN with:
     - PO number as `po_reference`
     - Vendor name as `supplier_name`
     - All PO line items matched to inventory items (by keyword)
     - Expected quantities from `po_items.qty`
     - Unit costs from `po_items.unit_price`
4. You are redirected to the GRN edit form. Verify and update:
   - Received quantities
   - Lot / batch / serial numbers
   - Expiry dates
   - Receiving location (defaults to the first active location)
5. Set status to **RECEIVED** to update stock levels, or **DRAFT** to save for later.

### Item matching

The bridge matches PO line item descriptions to inventory items using a keyword search (first 3 words of the description against `inv_items.item_name`). If no match is found for a line, it is skipped in the GRN — you can add it manually.

For reliable matching, ensure inventory item names reflect the standard descriptions used on POs.

---

## Escalating an Inventory Requisition to Procurement

When a departmental stock requisition cannot be fulfilled from existing inventory:

1. Open the requisition in **Inventory → Requisitions**.
2. Click **Escalate to Procurement** (visible when status is `SUBMITTED` and stock is insufficient).
3. Add escalation notes (optional).
4. The system creates a Draft **procurement_request** and links it via `inv_procurement_escalations`.
5. The requisition status changes to `ESCALATED_TO_PROCUREMENT`.
6. The Procurement Officer receives the draft request and processes it through the standard workflow.

The link between the requisition and the procurement request is visible on both records.

---

## Stock Level Awareness in Procurement

When a procurement request is being created, officers can search inventory stock levels to check whether the item is already in stock before committing to a purchase.

**API:**
```php
// In procurement/add.php — AJAX endpoint example:
$stockHits = ProcurementInventoryBridge::getStockForDescription($pdo, $searchKeyword);
// Returns items matching the keyword with current qty_on_hand
```

This prevents duplicate procurement of items that are already adequately stocked.

---

## Database Links

### `inv_goods_received.procurement_po_id`

Added by migration `020_procurement_inventory_link.sql`.

```sql
ALTER TABLE inv_goods_received
    ADD COLUMN procurement_po_id int(11) DEFAULT NULL,
    ADD CONSTRAINT fk_grn_procurement_po
        FOREIGN KEY (procurement_po_id)
        REFERENCES purchase_orders (po_id)
        ON DELETE SET NULL ON UPDATE CASCADE;
```

- Populated by `ProcurementInventoryBridge::createGrnFromPo()`
- `ON DELETE SET NULL` — if a PO is deleted the GRN is not deleted; the FK is nulled

### `inv_procurement_escalations`

Added by migration `020_procurement_inventory_link.sql`.

```sql
CREATE TABLE inv_procurement_escalations (
    escalation_id         INT AUTO_INCREMENT PRIMARY KEY,
    inv_requisition_id    INT NOT NULL,
    procurement_request_id INT DEFAULT NULL,
    escalated_by          INT NOT NULL,
    escalated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    escalation_notes      TEXT,
    status                ENUM('OPEN','LINKED','CANCELLED') DEFAULT 'OPEN',
    resolved_at           TIMESTAMP DEFAULT NULL,
    FOREIGN KEY (inv_requisition_id) REFERENCES inv_requisitions(requisition_id),
    FOREIGN KEY (procurement_request_id) REFERENCES procurement_requests(request_id)
);
```

---

## Service Layer Reference

**File:** `services/ProcurementInventoryBridge.php`

### `getPOForGrn(PDO $pdo, int $poId): ?array`

Returns PO header and line items for use in the GRN pre-fill form.

### `createGrnFromPo(PDO $pdo, int $poId, int $receivingLocationId, int $receivedByUserId): int`

Creates a Draft GRN populated from the given PO. Returns the `grn_id`.
Throws `RuntimeException` if the PO is not found or has no line items.

### `getStockForDescription(PDO $pdo, string $keyword): array`

Searches inventory for items matching `$keyword`. Returns up to 10 rows with `qty_on_hand` and `reorder_level`.

### `getGrnsForPo(PDO $pdo, int $poId): array`

Returns all GRNs linked to the given PO via `procurement_po_id`.

### `escalateRequisition(PDO $pdo, int $invRequisitionId, int $requestingBranchId, int $createdByUserId, string $notes = ''): int`

Escalates an inventory requisition to a procurement request. Returns the `escalation_id`.

### `getProcurementForEscalation(PDO $pdo, int $invRequisitionId): ?array`

Returns the linked procurement request for an escalated requisition.
