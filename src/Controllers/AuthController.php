<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller {
    public function register() {
        $this->view('auth/register', ['title' => 'Register']);
    }

    public function handleRegister() {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];

        // Validation (Basic)
        if (empty($username) || empty($email) || empty($password)) {
            $errors[] = "All fields are required.";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        // Logic
        if (empty($errors)) {
            $userModel = new User();
            if ($userModel->findByEmail($email)) {
                $errors[] = "Email already registered.";
            } elseif ($userModel->findByUsername($username)) {
                $errors[] = "Username already taken.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $token = bin2hex(random_bytes(32));

                if ($userModel->create($username, $email, $hashedPassword, $token)) {
                    // Send Email code here (TODO)
                    // For now, just logging it or simple redirect
                    $this->sendVerificationEmail($email, $token);
                    $this->redirect('/'); 
                    // Ideally redirect to a "check your email" page
                    return;
                } else {
                    $errors[] = "Registration failed. Please try again.";
                }
            }
        }

        $this->view('auth/register', [
            'title' => 'Register', 
            'errors' => $errors,
            'old' => ['username' => $username, 'email' => $email]
        ]);
    }

    public function verify() {
        $token = $_GET['token'] ?? '';
        $userModel = new User();
        
        if ($userModel->verifyUser($token)) {
            // In a real app, maybe log them in automatically or show success message on login page
            $this->redirect('/login?verified=1');
        } else {
            echo "Invalid or expired token.";
        }
    }

    public function login() {
        $data = ['title' => 'Login'];
        if (isset($_GET['verified'])) {
            $data['success'] = "Account verified! You can now login.";
        }
        $this->view('auth/login', $data);
    }

    public function handleLogin() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_verified']) {
                $this->view('auth/login', ['title' => 'Login', 'errors' => ['Account not verified. Check your email.']]);
                return;
            }
            
            // Session handling should be in Core or strict init
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            $this->redirect('/');
        } else {
            $this->view('auth/login', ['title' => 'Login', 'errors' => ['Invalid credentials.']]);
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        $this->redirect('/login');
    }


    public function forgotPassword() {
        $this->view('auth/forgot_password', ['title' => 'Forgot Password']);
    }

    public function handleForgotPassword() {
        $email = trim($_POST['email'] ?? '');
        $userModel = new User();
        
        if ($userModel->findByEmail($email)) {
            $token = bin2hex(random_bytes(32));
            $userModel->setResetToken($email, $token);
            $this->sendResetEmail($email, $token);
        }
        
        // Always show success to prevent email enumeration
        $this->view('auth/forgot_password', ['title' => 'Forgot Password', 'success' => 'If that email exists, we sent a reset link.']);
    }

    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        $userModel = new User();
        
        if ($user = $userModel->findByResetToken($token)) {
            $this->view('auth/reset_password', ['title' => 'Reset Password', 'token' => $token]);
        } else {
            echo "Invalid or expired token.";
        }
    }

    public function handleResetPassword() {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm) {
            $this->view('auth/reset_password', ['title' => 'Reset Password', 'token' => $token, 'errors' => ['Passwords do not match.']]);
            return;
        }

        $userModel = new User();
        if ($user = $userModel->findByResetToken($token)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $userModel->updatePassword($user['id'], $hashedPassword);
            $this->redirect('/login?reset=1');
        } else {
            echo "Invalid or expired token during submit.";
        }
    }

    private function sendResetEmail($email, $token) {
        $subject = "Reset your password";
        $link = "http://localhost:8080/reset-password?token=$token";
        $message = "Click here to reset: $link";
        $headers = "From: noreply@camagru.com" . "\r\n" .
                   "Reply-To: noreply@camagru.com" . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        mail($email, $subject, $message, $headers);
    }

    private function sendVerificationEmail($email, $token) {
        // ... (existing code)
        $subject = "Verify your account";
        $link = "http://localhost:8080/verify?token=$token";
        $message = "Click here to verify: $link";
        $headers = "From: noreply@camagru.com" . "\r\n" .
                   "Reply-To: noreply@camagru.com" . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        mail($email, $subject, $message, $headers);
    }
}
