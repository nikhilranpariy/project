<?php
session_start();
if (!isset($_SESSION["uid"])) {
    header("location:index.php");
    exit();
}

if (isset($_GET["st"])) {
    $trx_id = $_GET["tx"];
    $p_st = $_GET["st"];
    $amt = $_GET["amt"];
    $cc = $_GET["cc"];
    $cm_user_id = $_GET["cm"];
    $c_amt = isset($_COOKIE["ta"]) ? $_COOKIE["ta"] : 0; // Ensure the cookie is set

    if ($p_st == "Completed") {
        include_once("db.php");

        // Error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Debugging output
        echo "Transaction ID: $trx_id<br>";
        echo "Payment Status: $p_st<br>";
        echo "Amount: $amt<br>";
        echo "Currency: $cc<br>";
        echo "User ID: $cm_user_id<br>";

        // Fetch items from cart
        $sql = "SELECT p_id, qty FROM cart WHERE user_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $cm_user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product_id = [];
            $qty = [];

            while ($row = $result->fetch_assoc()) {
                $product_id[] = $row["p_id"];
                $qty[] = $row["qty"];
            }

            $stmt->close();

            // Insert orders
            for ($i = 0; $i < count($product_id); $i++) {
                $sql = "INSERT INTO orders (user_id, product_id, qty, trx_id, p_status) VALUES (?, ?, ?, ?, ?)";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("iiiss", $cm_user_id, $product_id[$i], $qty[$i], $trx_id, $p_st);
                if (!$stmt->execute()) {
                    echo "Error inserting order: " . $stmt->error;
                }
                $stmt->close();
            }

            // Delete items from cart
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $cm_user_id);
            if ($stmt->execute()) {
                ?>
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Khan Store</title>
                    <link rel="stylesheet" href="css/bootstrap.min.css"/>
                    <script src="js/jquery2.js"></script>
                    <script src="js/bootstrap.min.js"></script>
                    <script src="main.js"></script>
                    <style>
                        table tr td {padding: 10px;}
                    </style>
                </head>
                <body>
                <div class="navbar navbar-inverse navbar-fixed-top">
                    <div class="container-fluid">
                        <div class="navbar-header">
                            <a href="#" class="navbar-brand">Khan Store</a>
                        </div>
                        <ul class="nav navbar-nav">
                            <li><a href="index.php"><span class="glyphicon glyphicon-home"></span>Home</a></li>
                            <li><a href="profile.php"><span class="glyphicon glyphicon-modal-window"></span>Product</a></li>
                        </ul>
                    </div>
                </div>
                <p><br/></p>
                <p><br/></p>
                <p><br/></p>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <div class="panel panel-default">
                                <div class="panel-heading"></div>
                                <div class="panel-body">
                                    <h1>Thank you</h1>
                                    <hr/>
                                    <p>Hello <?php echo "<b>".$_SESSION["name"]."</b>"; ?>, Your payment process is
                                        successfully completed and your Transaction id is <b><?php echo $trx_id; ?></b><br/>
                                        You can continue your Shopping <br/></p>
                                    <a href="index.php" class="btn btn-success btn-lg">Continue Shopping</a>
                                </div>
                                <div class="panel-footer"></div>
                            </div>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>
                </body>
                </html>
                <?php
            } else {
                echo "Error deleting cart: " . $stmt->error;
            }
            $stmt->close();
        } else {
            header("location:index.php");
            exit();
        }
    }
}
?>
