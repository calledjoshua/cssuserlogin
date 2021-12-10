<?php 

    // First we execute our common code to connection to the database and start the session 
    require("common.php"); 
     
    // This variable will be used to re-display the user's username to them in the 
    // login form if they fail to enter the correct password.  It is initialized here 
    // to an empty value, which will be shown if the user has not submitted the form. 
    $submitted_username = ''; 
     
    // This if statement checks to determine whether the login form has been submitted 
    // If it has, then the login code is run, otherwise the form is displayed 
    if(!empty($_POST)) 
    { 
        // This query retreives the user's information from the database using 
        // their username. 
        $query = " 
            SELECT 
                id, 
                username, 
                password, 
                salt, 
                email 
            FROM users 
            WHERE 
                username = :username 
        "; 
         
        // The parameter values 
        $query_params = array( 
            ':username' => $_POST['username'] 
        ); 
         
        try 
        { 
            // Execute the query against the database 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params); 
        } 
        catch(PDOException $ex) 
        { 
            // Note: On a production website, you should not output $ex->getMessage(). 
            // It may provide an attacker with helpful information about your code.  
            die("Failed to run query: " . $ex->getMessage()); 
        } 
         
        // This variable tells us whether the user has successfully logged in or not. 
        // We initialize it to false, assuming they have not. 
        // If we determine that they have entered the right details, then we switch it to true. 
        $login_ok = false; 
         
        // Retrieve the user data from the database.  If $row is false, then the username 
        // they entered is not registered. 
        $row = $stmt->fetch(); 
        if($row) 
        { 
            // Using the password submitted by the user and the salt stored in the database, 
            // we now check to see whether the passwords match by hashing the submitted password 
            // and comparing it to the hashed version already stored in the database. 
            $check_password = hash('sha256', $_POST['password'] . $row['salt']); 
            for($round = 0; $round < 65536; $round++) 
            { 
                $check_password = hash('sha256', $check_password . $row['salt']); 
            } 
             
            if($check_password === $row['password']) 
            { 
                // If they do, then we flip this to true 
                $login_ok = true; 
            } 
        } 
         
        // If the user logged in successfully, then we send them to the private members-only page 
        // Otherwise, we display a login failed message and show the login form again 
        if($login_ok) 
        { 
            // Here I am preparing to store the $row array into the $_SESSION by 
            // removing the salt and password values from it.  Although $_SESSION is 
            // stored on the server-side, there is no reason to store sensitive values 
            // in it unless you have to.  Thus, it is best practice to remove these 
            // sensitive values first. 
            unset($row['salt']); 
            unset($row['password']); 
             
            // This stores the user's data into the session at the index 'user'. 
            // We will check this index on the private members-only page to determine whether 
            // or not the user is logged in.  We can also use it to retrieve 
            // the user's details. 
            $_SESSION['user'] = $row; 
             
            // Redirect the user to the private members-only page. 
            header("Location: ../index.html"); 
            die;
        } 
        else 
        { 
            // Tell the user they failed 
            print("Login Failed."); 
             
            // Show them their username again so all they have to do is enter a new 
            // password.  The use of htmlentities prevents XSS attacks.  You should 
            // always use htmlentities on user submitted values before displaying them 
            // to any users (including the user that submitted them).  For more information: 
            // http://en.wikipedia.org/wiki/XSS_attack 
            $submitted_username = htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8'); 
        } 
    } 
     
?> 

<!DOCTYPE html>
<html>
<head>
	
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ivory clo. &ndash; 2021</title>
    <link rel="icon" href="tabicon.png"  type="image/png">

    <link rel="stylesheet" href="assets/nav-menu.css">

    <link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.2/css/all.css" integrity="sha384-vSIIfh2YWi9wW0r9iZe7RJPrKwp6bG+s9QZMoITbCckVJqGCCRhc+ccxNcdpHuYu" crossorigin="anonymous">
    <link rel="stylesheet" href="https://projects.yves-steinbach.ch/pixl/assets/nav.css">

    <link rel="stylesheet" href="nav-menu.css">

    <div id= "rechteck"></div>
    <style>
        ::selection {
            color: #d1bc8a;
            background: #000;
        }
        body {
            cursor: crosshair;

            margin: 0;
            padding: 0;
            background: #000;
        }
        #background {
            z-index: -99;

            position: fixed;
            min-width: 100%; 
            min-height: 100%;

            opacity: 0.2;
        }
        button {
            transition: all 200ms;

            position: absolute;
            bottom: 120px;
            left: 50px;

            font-family: 'Work Sans', sans-serif;
            font-weight: 500;
            font-size: 16px;

            color: #fff;

            padding: 18px 32px 18px 32px;

            background: transparent;
            border: 1px solid #fff;
            outline: none;
        }
        button:hover {
            cursor: pointer;
            
            color: #000;
            background: #d1bc8a;
            border: 1px solid #d1bc8a;
        }
        button i {
            padding-left: 10px;
        }

        #rechteck {
            width: 400px;
            height: 400px;
            background-color: #222;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-40%, -40%);
            border-radius: 20px;
            border-color: grey;
            border-width: 2px;
            border-style: solid;
            
        }
        

        #login {
            width: 100px;
            height:100px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%)
        }

        #register {
            width: 100px;
            height: 100px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(60%, 82%)
        }

        #login1 {
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-2%, -120%)
        }

        #password {
            color: white;
        }

        #username {
            color: white;
        }

        /*------------------------------------------------------------------------------------------------*/

        #information h1 {
            position: absolute;
            bottom: 250px;
            left: 50px;

            font-family: 'Work Sans', sans-serif;
            font-weight: 700;
            font-size: 50px;

            color: #fff;
        }
        #information p {
            position: absolute;
            bottom: 200px;
            left: 50px;

            font-family: 'Space Grotesk', sans-serif;
            font-weight: 500;
            font-size: 16px;

            color: #fff;
        }

        /*------------------------------------------------------------------------------------------------*/

        /* Mobile */
        #mobile {
            z-index: 99;
            
            display: none;

            position: fixed;
            bottom: 0;
            height: 100px;
            width: 100%;

            background: #000;
        }
        #mobile ul {
            display: flex;
            justify-content: center;
            align-items: center;

            margin-top: 10px;
            margin-left: -150px;
        }
        #mobile li {
            padding: 0px 25px 0px 25px;
            display: inline;
        }
        #mobile li a {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 21px;

            text-decoration: none;

            color: #ccc;
        }
        #mobile li a:hover {
            cursor: pointer;

            border: none;
            border-bottom: 1px solid #fff;
            color: #fff;
        }
        /* Mobile */

        @media screen and (max-width: 550px) {
            nav {
                display: none;
            }
            #mobile {
                display: block;
            }
            #mobile img {
                position: fixed;
                top: 35px;
                left: 35px;
            }
            #information  h1 {
                bottom: 325px;
                left: 35px;
            }
            #information p {
                bottom: 250px;
                left: 35px;
            }
            button {
                bottom: 180px;
                left: 35px;
            }
        }
	</style>
	
</head>
<body>

    <nav>
        <a href="https://joshua-jane.ch"><img src="ivoryclowhite.png" alt="Logo" width="150px"></a>
        <ul>
            <li><a class="active" href="https://joshua-jane.ch">Home</a></li>
            <li><a href="projects.html">Projects</a></li>
            <li><a href="playlists.html">Playlists</a></li>
            <li><a href="mailto:contact@business.ivoryclothing@gmail.com">Contact</a></li>
        </ul>
    </nav>

    <div id="mobile">
        <a href="https://joshua-jane.ch/"><img src="ivoryclo.png" alt="Logo" width="70px"></a>
        <ul>
            <li><a class="active" href="https://joshua-jane.ch">Home</a></li>
            <li><a class="projects" href="projects">Projects</a></li>
            <li><a class="playlists" href="playlists">Playlists</a></li>
        </ul>
    </div>

    <video autoplay muted loop id="background">
			<source src="glitch effect black screen template.mp4" type="video/mp4">
			Your browser does not support HTML5 video.
		</video>

        <div id="login1" class="inhalt">
        <h1>Login</h1> 
    </div>
<form action="login.php" method="post" id="login"> 
    <div id="username" class="username1">
        <br>
    Username:
    <input type="text" name="username" value=""<?php echo $submitted_username; ?> /> 
    <br /><br /> 
    Password:<br/> 
    <input type="password" name="password" value=""/> 
    <br /><br /> 
    <input type="submit" value="Login"/> 
</form> 
<form action=register.php id="register">
<input type="submit" value="Register">
    </form>
</body>
</html>
</body>


