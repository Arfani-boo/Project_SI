<?php
include 'koneksi.php';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah terdaftar!');</script>";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        
        $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password_hash', 'user')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Pendaftaran Berhasil! Silakan Login.'); window.location='login.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Wisata Madura</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <h4>Daftar Akun</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="register" class="btn btn-primary w-100">Daftar</button>
                        </form>
                        <div class="mt-3 text-center">
                            <small>Sudah punya akun? <a href="login.php">Login disini</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>