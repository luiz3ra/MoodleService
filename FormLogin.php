<?php
require_once('app/controller/MoodleController.php');

$moodleCtrl = new MoodleController();
?>

<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
        <form action="http://fcv.edu.br/ead/moodle/login/index.php" method="post" accept-charset="utf-8" id="formLoginAlunosnet">
            <input name="loginava" type="hidden" value="alunosnet">
            <input type="hidden" id="username" name="username" value="<?php echo $moodleCtrl->Decrypting($_GET["username"]); ?>">
            <input type="hidden" id="password" name="password" value="<?php echo $moodleCtrl->Decrypting($_GET["password"]); ?>">
        </form> 

        <script type="text/javascript">
            document.getElementById("formLoginAlunosnet").submit();
        </script>
    </body>
</html>