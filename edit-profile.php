<?php

require_once 'scripts/php-scripts/connectionDB.php';
require_once 'classes/user-class.php';
require_once 'classes/post-class.php';
require_once 'classes/search-class.php';

if (!$conn) die("Neuspesna konekcija sa bazom");

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

$user =  User::load_user_data($_SESSION['user_id'], $conn);
$img = $user->get_photo_path();
$des = $user->get_description();


$upload_dir = 'images/' . $user->get_username();
$new_img_path = $img;
$description = null;

if (array_key_exists('cancle', $_POST)) {
    header('location: profile.php');
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && array_key_exists('save', $_POST)) {
    if ($_POST['description'] != null) {
        $description = $_POST['description'];
    }
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $time = date("d-m-Y") . "-" . time();
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_name = $time . "-" . basename($_FILES['file']['name']);

        // Kreiranje direktorijuma ako ne postoji
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $upload_path = $upload_dir . '/' . $file_name;
        // Premestanje slike u odredišni direktorijum
        if (move_uploaded_file($file_tmp, $upload_path)) {
            // Novi put do slike
            $new_img_path = $upload_path;
            // Ažuriranje puta do slike u bazi podataka ili gde vam je potrebno
            // $user->update_photo_path($new_img_path);
            // Možete dodati funkciju za ažuriranje puta do slike u vašoj User klasi
            $img = $new_img_path; // Ažurirajte put do slike koja se prikazuje na stranici
        } else {
            echo "Greška prilikom otpremanja slike.";
        }
    }
    User::save_changes($conn, $name, $lastname, $description, $img, $_SESSION['user_id']);
    header('location: profile.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/edit.css">
</head>

<body>
    <div class="container">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="img-wrapper">
                <div class="img" id="img" style=" background-image: url('<?php echo $img; ?>'); background-size: cover; background-repeat: no-repeat; background-position: center;"></div>
                <input type="file" name="file" id="file" onchange="ucitajFile()">
            </div><br>
            <div class="column">
                <div class="cl1">
                    <input class="input-name" type="text" name="username" id="" value="<?php echo $user->get_username() ?>" disabled><br>
                    <input type="text" name="name" id="" value="<?php echo $user->get_name() ?>" placeholder="name"><br>
                    <input type="text" name="lastname" id="" value="<?php echo $user->get_lastname() ?>" placeholder="lastname"><br>
                    <input class="input-description" type="text" name="description" id="" value="<?php if ($user->get_description() == 'no description') {
                                                                                                        echo null;
                                                                                                    } else {
                                                                                                        echo $user->get_description();
                                                                                                    } ?>" placeholder="description...">

                </div>
            </div>
            <br>
            <div class="buttons">
                <input name="save" class="save" type="submit" value="save">
                <input class="cancle" name="cancle" type="submit" value="cancle">
            </div>
        </form>
    </div>
    <script>
        function ucitajFile() {
            let file1 = document.getElementById("file");
            if (file1.files.length !== 0 && file1.files[0].type.match(/image.*/)) {
                let fajl = file1.files[0];
                let reader = new FileReader();
                reader.readAsDataURL(fajl);
                reader.onload = function(e) {
                    let img = document.getElementById("img");
                    img.style.backgroundImage = "url(" + e.target.result + ")";
                };

                reader.error = function() {
                    alert("Greska pr ciranju fajla");
                };
            } else {
                alert("Greska pri citanju fajla 1")
            }
        }
    </script>
</body>

</html>