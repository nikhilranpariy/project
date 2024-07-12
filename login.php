<?php
include "db.php";

session_start();

if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $password = $_POST["password"];
    
    // Check if the user is logging in
    $stmt = $con->prepare("SELECT * FROM user_info WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION["uid"] = $row["user_id"];
        $_SESSION["name"] = $row["first_name"];
        $ip_add = getenv("REMOTE_ADDR");

        if (isset($_COOKIE["product_list"])) {
            $p_list = stripcslashes($_COOKIE["product_list"]);
            $product_list = json_decode($p_list, true);

            foreach ($product_list as $product_id) {
                $stmt = $con->prepare("SELECT id FROM cart WHERE user_id = ? AND p_id = ?");
                $stmt->bind_param("ii", $_SESSION["uid"], $product_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows < 1) {
                    $stmt = $con->prepare("UPDATE cart SET user_id = ? WHERE ip_add = ? AND user_id = -1");
                    $stmt->bind_param("is", $_SESSION["uid"], $ip_add);
                    $stmt->execute();
                } else {
                    $stmt = $con->prepare("DELETE FROM cart WHERE user_id = -1 AND ip_add = ? AND p_id = ?");
                    $stmt->bind_param("si", $ip_add, $product_id);
                    $stmt->execute();
                }
            }

            setcookie("product_list", "", time() - 3600, "/");
            echo "cart_login";
            exit();
        }

        echo "login_success";

        $BackToMyPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        header('Location: ' . $BackToMyPage);
        exit();
    } else {
        // Check if the admin is logging in
        $passwordHash = md5($_POST["password"]);
        $stmt = $con->prepare("SELECT * FROM admin_info WHERE admin_email = ? AND admin_password = ?");
        $stmt->bind_param("ss", $email, $passwordHash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $_SESSION["uid"] = $row["admin_id"];
            $_SESSION["name"] = $row["admin_name"];
            echo "login_success";
            echo "<script> location.href='admin/addproduct.php'; </script>";
            exit();
        } else {
            echo "<span style='color:red;'>Please register before login..!</span>";
            exit();
        }
    }

    $stmt->close();
}
?>
