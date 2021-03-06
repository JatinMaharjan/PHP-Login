<?php

function redirect($location) {
    return header("location:" . $location);
}

function escape($string) {
    global $connection;
    return mysqli_real_escape_string($connection, trim($string));
}

function confirm($result) {
    global $connection;
if(!$result) {
    die ("Query Failed. " . mysqli_error($connection));
    }
}

function recordCount($table) {
    global $connection;
    $query = "SELECT * FROM " . $table;
    $sel_all_post = mysqli_query($connection, $query);
    return mysqli_num_rows($sel_all_post);
}

function contactCount($id) {
    global $connection;
    $query = "select * from contacts where contact_user_id=" . $id;
    $sel_all_post = mysqli_query($connection, $query);
    return mysqli_num_rows($sel_all_post);
}


function is_admin($username) {
    global $connection;
    $query = "select user_role from users where username = '$username'";
    $result =  mysqli_query($connection, $query);
    confirm($result);

    $row = mysqli_fetch_array($result);
    if ($row['user_role'] == 'admin') {
        return true;
    }else {
        return false;
    }

}

function username_exists($username) {
    global $connection;
    $query = "select username from users where username = '$username'";
    $result =  mysqli_query($connection, $query);
    confirm($result);
    if (mysqli_num_rows($result) > 0) {
        return true;
    } else {
        return false;
    }

}

function email_exists($user_email) {
    global $connection;
    $query = "select user_email from users where user_email = '$user_email'";
    $result =  mysqli_query($connection, $query);
    confirm($result);
    if (mysqli_num_rows($result) > 0) {
        return true;
    } else {
        return false;
    }

}

function register_user($username, $user_firstname, $user_lastname, $user_email, $password,$user_address) {
global $connection;
$username = $_POST['username'];
$user_firstname = $_POST['firstname'];
$user_lastname = $_POST['lastname'];
$user_email = $_POST['email'];
$password = $_POST['password'];
$user_address = $_POST['address'];
// $user_contact_no = $_POST['contact'];

$username      = escape($username);
$user_firstname = escape($user_firstname);
$user_lastname  = escape($user_lastname);
$user_email    = escape($user_email);
$password      = escape($password);
$user_address      = escape($user_address);
// $user_contact_no      = mysqli_real_escape_string($connection, $user_contact_no);
$password = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));

$query = "INSERT INTO users(username, user_firstname, user_lastname, user_email, user_password, user_address) ";
$query .= "VALUES('$username', '$user_firstname', '$user_lastname', '$user_email', '$password','$user_address' )";
$register_user_query = mysqli_query($connection, $query);

$query = "select user_id from users where username = '{$username}' ";
$sel_userid_query = mysqli_query($connection, $query);
while ($row = mysqli_fetch_array($sel_userid_query)) {
    $user_id = $row['user_id'];
}

$log_action= mysqli_real_escape_string($connection,"new User Registered");

create_log($username, $user_id, $log_action); 

confirm($register_user_query);
if (mysqli_affected_rows($register_user_query = 1)) {
    login_user($username, $password);
    redirect("index.php");
}

}

function login_user($username, $password) {
    global $connection;
    $username = trim($username);
    $password = trim($password);
    $username = escape($username);
    $password = escape($password);

    $query = "select * from users where username = '{$username}' ";
    $sel_username_query = mysqli_query($connection, $query);

    if(!$sel_username_query) {
        die("QUERY Failed". mysqli_error($connection) );
    }

    while ($row = mysqli_fetch_array($sel_username_query)) {
        $db_user_id = $row['user_id'];
        $db_username = $row['username'];
        $db_user_password = $row['user_password'];
        $db_user_firstname = $row['user_firstname'];
        $db_user_lastname = $row['user_lastname'];
        $db_user_email = $row['user_email'];
        $db_user_role = $row['user_role'];

    }

    if(password_verify($password, $db_user_password)){
        $_SESSION['user_id'] = $db_user_id;
        $_SESSION['user_password'] = $db_user_password;
        $_SESSION['username'] = $db_username;
        $_SESSION['firstname'] = $db_user_firstname;
        $_SESSION['lastname'] = $db_user_lastname;
        $_SESSION['email'] = $db_user_email;
        $_SESSION['user_role'] = $db_user_role;
        $log_action="loggedin";

        header("location: dashboard.php");
        create_log($_SESSION['username'],$_SESSION['user_id'], $log_action); 

    } else {
    // redirect("login.php");
    echo "username or password wrong";

    }
}

function isLoggedIn() {
    if (isset($_SESSION['user_role'])) {
        return true;
    }
    return false;
}

function create_log($log_username, $log_user_id, $log_action) {
    global $connection;
    $log_username = $log_username;
    $log_username = $log_username;
    $log_action = $log_action;


    $log_action  = escape($log_action);

    $query = "INSERT INTO logs(log_user_id, log_username, log_action) ";
    $query .= "VALUES('$log_user_id', '$log_username', '$log_action')";
    $register_log_query = mysqli_query($connection, $query);

}


?>