<style>
    /* SIDEBAR */
.sidebar{
    width:260px;
    min-width:260px;
    height:100vh;
    position:fixed;
    top:0;
    left:0;
    background:#050505;
    border-right:1px solid #1a1a1a;
    display:flex;
    flex-direction:column;
    padding:20px;
    box-sizing:border-box;
    overflow-y:auto;
    z-index:100;
}

/* PROFILE BOX */
.profile-box{
    text-align:center;
    padding-bottom:20px;
    border-bottom:1px solid #1a1a1a;
}

/* AVATAR */
.avatar{
    width:85px;
    height:85px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #e60012;
    box-shadow:0 0 15px rgba(230,0,18,0.6);
    margin-bottom:10px;
    transition:0.3s;
}

.avatar:hover{
    transform:scale(1.05);
}

/* USERNAME */
.username{
    font-weight:600;
    letter-spacing:0.5px;
    color:#fff;
    margin-top:5px;
}

/* SMALL TEXT */
.small{
    font-size:12px;
    color:#888;
    margin-top:3px;
}

/* EDIT BUTTON */
.btn-edit{
    width:100%;
    margin-top:15px;
    padding:10px;
    background:#e60012;
    border:none;
    color:#fff;
    border-radius:6px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.btn-edit:hover{
    background:#ff1a2d;
    box-shadow:0 0 12px rgba(230,0,18,0.6);
}

/* MENU */
.menu{
    margin-top:20px;
    display:flex;
    flex-direction:column;
    gap:8px;
}

.menu-btn{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 14px;
    text-decoration:none;
    color:#fff;
    border-radius:8px;
    transition:0.25s;
    position:relative;
    font-size:14px;
    font-weight:500;
}

.menu-btn i{
    width:20px;
    text-align:center;
    transition:0.3s;
}

.menu-btn:hover{
    background:#111;
    color:#e60012;
    transform:translateX(5px);
}

.menu-btn:hover i{
    color:#e60012;
}

.menu-btn.active{
    background:#111;
    color:#e60012;
    border-left:3px solid #e60012;
}

.menu-btn.active i{
    color:#e60012;
}
</style>

<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="profile-box">
        <img src="../uploads/<?= $user['profile_pic'] ?: 'default.png' ?>" class="avatar">

        <div class="username">
            <?= htmlspecialchars($user['fullname']) ?>
        </div>

        <div class="small">
            <?= htmlspecialchars($user['email']) ?>
        </div>

        <div class="small">
            Member since: <?= date("Y") ?>
        </div>

        <a href="profile.php">
            <button class="btn-edit">Edit Profile</button>
        </a>
    </div>

    <nav class="menu">

        <a href="user_dashboard.php"
           class="menu-btn <?= basename($_SERVER['PHP_SELF']) == 'user_dashboard.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-user"></i>
            Profile Status
        </a>

        <a href="my_testdrives.php"
           class="menu-btn <?= basename($_SERVER['PHP_SELF']) == 'my_testdrives.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-check"></i>
            Test Drive Request
        </a>

        <a href="saved_vehicles.php"
           class="menu-btn <?= basename($_SERVER['PHP_SELF']) == 'saved_vehicles.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-heart"></i>
            Saved Vehicles
        </a>

    </nav>

</aside>