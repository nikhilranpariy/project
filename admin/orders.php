<?php
session_start();
include("../db.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $order_id = $_GET['order_id'];
    
    // Sanitize the input
    $order_id = mysqli_real_escape_string($con, $order_id);

    // Delete query
    $delete_query = "DELETE FROM orders WHERE order_id = '$order_id'";
    if (mysqli_query($con, $delete_query)) {
        echo "Order deleted successfully.";
    } else {
        echo "Error deleting order: " . mysqli_error($con);
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page1 = ($page == "" || $page == "1") ? 0 : ($page * 10) - 10;

include "sidenav.php";
include "topheader.php";
?>
<!-- End Navbar -->
<div class="content">
    <div class="container-fluid">
        <!-- your content here -->
        <div class="col-md-14">
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Orders / Page <?php echo $page; ?></h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive ps">
                        <table class="table table-hover tablesorter" id="">
                            <thead class="text-primary">
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Products</th>
                                    <th>Contact | Email</th>
                                    <th>Address</th>
                                    <th>Details</th>
                                    <th>Shipping</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $query = "SELECT orders.order_id, products.product_title, user_info.first_name, user_info.mobile, user_info.email, user_info.address1, user_info.address2, orders.qty 
                                          FROM orders 
                                          JOIN products ON orders.product_id = products.product_id 
                                          JOIN user_info ON orders.user_id = user_info.user_id 
                                          LIMIT $page1, 10";

                                // Debugging: Print the SQL query
                                echo "<pre>$query</pre>";

                                $result = mysqli_query($con, $query);
                                
                                if (!$result) {
                                    die("Query 1 incorrect: " . mysqli_error($con));
                                }

                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Add a placeholder for 'Time' if there's no such column in the orders table
                                    echo "<tr>
                                            <td>{$row['first_name']}</td>
                                            <td>{$row['product_title']}</td>
                                            <td>{$row['email']}<br>{$row['mobile']}</td>
                                            <td>{$row['address1']}<br>{$row['address2']}</td>
                                            <td>{$row['qty']}</td>
                                            <td>Time Placeholder</td> <!-- Update this if you find the correct column -->
                                            <td>
                                                <a class='btn btn-danger' href='orders.php?order_id={$row['order_id']}&action=delete'>Delete</a>
                                            </td>
                                        </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                        </div>
                        <div class="ps__rail-y" style="top: 0px; right: 0px;">
                            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include "footer.php";
?>
