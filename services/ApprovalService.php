<?php

class ApprovalService {

    private $pdo;
    private $user_id;
    private $role;

    public function __construct($pdo, $user_id, $role) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
        $this->role = $role;
    }

    public function approve($entity_type, $entity_id) {

        // 1️⃣ Get next required stage
        $stmt = $this->pdo->prepare("
            SELECT MIN(stage_order) as next_stage
            FROM request_approvals
            WHERE entity_type = ?
              AND entity_id = ?
              AND status = 'pending'
        ");
        $stmt->execute([$entity_type, $entity_id]);
        $next_stage = $stmt->fetchColumn();

        if (!$next_stage) {
            throw new Exception("No pending approval stages.");
        }

        // 2️⃣ Check if current user matches next stage
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM request_approvals
            WHERE entity_type = ?
              AND entity_id = ?
              AND role = ?
              AND stage_order = ?
              AND status = 'pending'
        ");
        $stmt->execute([
            $entity_type,
            $entity_id,
            $this->role,
            $next_stage
        ]);

        $approval = $stmt->fetch();

        if (!$approval) {
            throw new Exception("You cannot approve out of sequence.");
        }

        // 3️⃣ Approve
        $this->pdo->prepare("
            UPDATE request_approvals
            SET status='approved',
                approved_by=?,
                approved_at=NOW()
            WHERE id=?
        ")->execute([$this->user_id, $approval['id']]);

        return $this->isFullyApproved($entity_type, $entity_id);
    }

    public function isFullyApproved($entity_type, $entity_id) {

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM request_approvals
            WHERE entity_type = ?
              AND entity_id = ?
              AND status = 'pending'
        ");
        $stmt->execute([$entity_type, $entity_id]);

        return $stmt->fetchColumn() == 0;
    }

    public function reject($entity_type, $entity_id, $reason) {

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM request_approvals
            WHERE entity_type = ?
              AND entity_id = ?
              AND role = ?
              AND status = 'pending'
            ORDER BY stage_order ASC
            LIMIT 1
        ");
        $stmt->execute([
            $entity_type,
            $entity_id,
            $this->role
        ]);

        $approval = $stmt->fetch();

        if (!$approval) {
            throw new Exception("Not authorized to reject this stage.");
        }

        $this->pdo->prepare("
            UPDATE request_approvals
            SET status='rejected',
                rejection_reason=?,
                approved_by=?,
                approved_at=NOW()
            WHERE id=?
        ")->execute([
            $reason,
            $this->user_id,
            $approval['id']
        ]);
    }
}
