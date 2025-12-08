<?php

declare(strict_types=1);
namespace App\Models;

use App\Model;

class Invoice extends Model
{
    public function create(
        float $amount,
        int $userId
    ): int {
        $newInvoiceStmt = $this->db->prepare('INSERT INTO invoices
                                            (amount, user_id)
                                            VALUES
                                            (?, ?)');
            
        $newInvoiceStmt->execute([$amount, $userId]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function find(int $invoiceId)
    {
        $stmt = $this->db->prepare('SELECT invoices.id, amount, full_name
                                    FROM invoices
                                    LEFT JOIN users ON users.id = user_id
                                    WHERE invoices.id = ?');
        
        $stmt->execute([$invoiceId]);
        
        $invoice = $stmt->fetch();
        
        return $invoice ? $invoice : [];
    }
}