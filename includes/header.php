<nav>
    
    <ul>
        <li class="logo-smart"><b><i>SMART SHELF</i></b></li>
        <li class="dash"><a href="dashboard.php">Dashboard</a></li>
        <li><a href="add_product.php">Add Product</a></li>
        <li><a href="update_product.php">Update Product</a></li>
        <li><a href="delete_product.php">Delete Product</a></li>
        
        <li><a href="manager_tools.php">Manager Tools</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</nav>

<style>
    /* Base Styles for Navbar */
    .dash{
        margin-left: 350px;
    }
    .logo-smart {
        color: #fff;
        text-decoration: none;
        font-size: 18px;
        font-weight: bold; 
        padding-left: 5px;      
    }
    nav {
        /* background-color: #A57C65;   */
        background-color: rgb(0, 27, 66);  
        padding: 29px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        animation: fadeIn 1s ease-in-out;
        z-index: 1000;
        position: sticky;
        top: 0;
    }

   

    @keyframes brandAnimation {
        0% { opacity: 0; transform: translateX(-20px); }
        100% { opacity: 1; transform: translateX(0); }
    }

    /* Navigation Menu */
    nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
    }

    nav ul li {
        margin: 0 20px;
        position: relative;
    }

    nav ul li a {
        color: #fff;
        text-decoration: none;
        font-size: 15px;
        font-weight: bold;
        position: relative;
        transition: color 0.3s, transform 0.3s;
    }

    nav ul li a::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0%;
        height: 2px;
        background-color: #ffb74d;
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    nav ul li a:hover {
        color:#fff;
    }

    nav ul li a:hover::before {
        width: 100%;
    }

    /* Hover Animation for Menu Items */
    @keyframes slideIn {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    nav ul li {
        animation: slideIn 0.6s ease-out;;
    }

    /* Mobile Menu Adjustments */
    @media (max-width: 768px) {
        nav {
            flex-direction: column;
            text-align: center;
        }

        nav ul {
            flex-direction: column;
        }

        nav ul li {
            margin: 10px 0;
        }

        .navbar-brand {
            margin-bottom: 20px;
        }
    }
</style>


