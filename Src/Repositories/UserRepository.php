<?php
namespace Src\Repositories;

use PDO;
use Src\Config\Database;
use Throwable;

class UserRepository
{
    private PDO $db;

    public function __construct(array $cfg)
    {
        $this->db = Database::conn($cfg);
    }

    /**
     * Paginate data dengan fitur pencarian dan sorting.
     */
    public function paginate($page, $per, $search = '', $sortBy = 'id', $sortDir = 'DESC')
    {
        // Validasi kolom dan arah sort agar aman dari SQL Injection
        $allowedSort = ['id', 'name', 'email', 'role', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'id';
        }
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $off = ($page - 1) * $per;

        // Query pencarian
        $where = '';
        $params = [];
        if (!empty($search)) {
            $where = 'WHERE name LIKE :search OR email LIKE :search OR role LIKE :search';
            $params[':search'] = "%$search%";
        }

        // Hitung total data
        $countSql = "SELECT COUNT(*) FROM users $where";
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Query data dengan sort & limit
        $sql = "SELECT id, name, email, role, created_at, updated_at
                FROM users
                $where
                ORDER BY $sortBy $sortDir
                LIMIT :per OFFSET :off";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':per', (int)$per, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$off, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $per,
                'last_page' => max(1, (int)ceil($total / $per)),
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir
            ]
        ];
    }

    public function find($id)
    {
        $s = $this->db->prepare('SELECT id, name, email, role, created_at, updated_at FROM users WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function create($name, $email, $hash, $role = 'user')
    {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare('INSERT INTO users(name, email, password_hash, role) VALUES(?, ?, ?, ?)');
            $s->execute([$name, $email, $hash, $role]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $this->find($id);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $name, $email, $role)
    {
        $s = $this->db->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
        $s->execute([$name, $email, $role, $id]);
        return $this->find($id);
    }

    public function delete($id)
    {
        $s = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $s->execute([$id]);
    }
}
