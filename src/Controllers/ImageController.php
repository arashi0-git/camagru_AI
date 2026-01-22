<?php
namespace Src\Controllers;

use Src\Core\Controller;
use Src\Models\Image;

class ImageController extends Controller {
    
    private $imageModel;

    public function __construct() {
        $this->imageModel = new Image();
    }

    public function editor() {
        // Ensure user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        // Get list of stickers
        $stickers = $this->getStickers();

        // Get user's images for gallery
        $userImages = $this->imageModel->getByUserId($_SESSION['user_id']);

        $this->view('camera/editor', [
            'title' => 'Camera Editor', 
            'stickers' => $stickers,
            'userImages' => $userImages
        ]);
    }

    public function upload() {
        // Handle file upload
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) $this->redirect('/login');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
            $file = $_FILES['image'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($ext, $allowed)) {
                    $filename = uniqid() . '.' . $ext;
                    $uploadPath = __DIR__ . '/../../public/uploads/' . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        // Check for sticker application
                        if (isset($_POST['sticker']) && !empty($_POST['sticker'])) {
                            $this->applySticker($uploadPath, $_POST['sticker']);
                        }

                        // Save to DB
                        $this->imageModel->create($_SESSION['user_id'], '/uploads/' . $filename);

                        $this->view('camera/editor', [
                            'title' => 'Camera Editor', 
                            'uploaded_image' => '/uploads/' . $filename,
                            'stickers' => $this->getStickers(),
                            'userImages' => $this->imageModel->getByUserId($_SESSION['user_id'])
                        ]);
                        return;
                    }
                }
            }
            $this->view('camera/editor', [
                'title' => 'Camera Editor', 
                'error' => 'Invalid file or upload failed.',
                'stickers' => $this->getStickers(),
                'userImages' => $this->imageModel->getByUserId($_SESSION['user_id'])
            ]);
        }
    }

    public function save() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
             header('Content-Type: application/json');
             echo json_encode(['success' => false, 'error' => 'Unauthorized']);
             exit;
        }

        // Handle webcam capture (Base64)
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['image'])) {
            $image_parts = explode(";base64,", $data['image']);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1] ?? 'png';
            $image_base64 = base64_decode($image_parts[1]);
            
            $filename = uniqid() . '.' . $image_type;
            $filePath = __DIR__ . '/../../public/uploads/' . $filename;
            
            file_put_contents($filePath, $image_base64);

            // Apply sticker if present
            if (isset($data['sticker']) && !empty($data['sticker'])) {
                $this->applySticker($filePath, $data['sticker']);
            }
            
            // Save to DB
            $this->imageModel->create($_SESSION['user_id'], '/uploads/' . $filename);
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'image_url' => '/uploads/' . $filename]);
            exit;
        }
    }

    private function getStickers() {
        $stickers = [];
        $stickerDir = __DIR__ . '/../../public/stickers';
        if (is_dir($stickerDir)) {
            $files = scandir($stickerDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $stickers[] = '/stickers/' . $file;
                }
            }
        }
        return $stickers;
    }

    private function applySticker($sourcePath, $stickerShortPath) {
        $stickerPath = __DIR__ . '/../../public' . $stickerShortPath;
        
        if (!file_exists($stickerPath) || !file_exists($sourcePath)) {
            return;
        }

        $src = imagecreatefromstring(file_get_contents($sourcePath));
        $sticker = imagecreatefrompng($stickerPath);

        if (!$src || !$sticker) return;

        $src_w = imagesx($src);
        $src_h = imagesy($src);
        $sticker_w = imagesx($sticker);
        $sticker_h = imagesy($sticker);

        $new_sticker_w = $src_w * 0.3;
        $ratio = $new_sticker_w / $sticker_w;
        $new_sticker_h = $sticker_h * $ratio;

        $dst_x = ($src_w - $new_sticker_w) / 2;
        $dst_y = ($src_h - $new_sticker_h) / 2;

        imagecopyresampled($src, $sticker, $dst_x, $dst_y, 0, 0, $new_sticker_w, $new_sticker_h, $sticker_w, $sticker_h);

        $info = getimagesize($sourcePath);
        if ($info['mime'] == 'image/jpeg') {
            imagejpeg($src, $sourcePath);
        } elseif ($info['mime'] == 'image/png') {
            imagepng($src, $sourcePath);
        }

        imagedestroy($src);
        imagedestroy($sticker);
    }
}
