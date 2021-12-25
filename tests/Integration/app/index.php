<?php

$inserted = false;

if (isset($_POST['name']) && isset($_POST['age'])) {
    $pdo = new PDO('sqlite:'.__DIR__.'/db.sqlite');
    $sql = "INSERT INTO persons ('name', 'age') VALUES ('{$_POST['name']}', {$_POST['age']})";

    $query = $pdo->query($sql);

    if ($query) {
        $inserted = true;
    } else {
        die($pdo->errorInfo());
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php if ($inserted) : ?>
        <p class="info">Insertion successful.</p>
    <?php endif ?>

    <form action="/" method="post">
        <input id="name" type="text" name="name" placeholder="Name">
        <input id="age" type="number" name="age" placeholder="Age">
        <button id="submit" type="submit">Insert</button>
    </form>
</body>
</html>