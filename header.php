<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<title>Dashboard Header</title>
<style>
/* General reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    border-radius:10px;
}

body {
    background-color: #f4f7f9;
}

/* Header styling */
.header {
    width:  left 70%;
    background-color: #007bff;
    color: #fff;
    padding: 15px 20px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

/* User info inside header */
.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 1rem;
}

/* Welcome text */
.user-info span {
    font-weight: 500;
}

/* Logout button */
.logout-btn {
    padding: 8px 14px;
    background-color: #ff4d4f;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    transition: background 0.3s;
}

.logout-btn:hover {
    background-color: #d9363e;
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .header {
        flex-direction: column;
        align-items: flex-start;
        padding: 10px 15px;
    }
    .user-info {
        gap: 10px;
        font-size: 0.9rem;
    }
    .logout-btn {
        padding: 6px 12px;
        font-size: 0.9rem;
    }
}
</style>
</head>
<body>
<div class="header">
    <div class="user-info">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php" class="logout-btn"><i class="fa fa-sign-out"></i> Logout</a>
    </div>
</div>
</body>
</html>
