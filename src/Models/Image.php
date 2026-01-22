<?php
namespace Src\Models;

use Src\Core\Model;
use PDO;

class Image extends Model {
    public function create($userId, $path) {
        $stmt = $this->db->prepare("INSERT INTO images (user_id, path) VALUES (:user_id, :path)");
        return $stmt->execute([
            ':user_id' => $userId,
            ':path' => $path
        ]);
    }

    public function getByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM images WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
