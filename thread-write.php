<?php
session_start();
 require('db-connect.php');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>新規スレッド作成</title>
</head>
<body>
    <?php
    $db=new PDO($connect,USER,PASS);
    $stmt = $db->prepare('SELECT * FROM genre');
    $stmt->execute();
    $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $flag=0;
    $a=[];
    $name=[];
    foreach($genres as $genre){
        $a[]=$genre['genre_id'];
        $name[]=$genre['genre_name'];
    }
    $b=count($a);
    ?>
    <form action="thread-confirm.php" method="POST">
        <div>タイトル</div>
        <textarea name="title" id="" cols="50" rows="10"></textarea>
        <div>ジャンル</div>
        <?php
        for($i = 0; $i<$b ; $i++){
        echo '<input type="radio" name="genre_id" value="',$a[$i],'">',$name[$i],'<br>';
        echo '</div>';
    }
    ?>
    </div>
        <input type="submit" value="送信"></div>
    </form>
    <a href="a.php">戻る</a>
</body>
</html>