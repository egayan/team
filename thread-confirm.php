<?php
session_start();
require('db-connect.php');
$pdo=new PDO($connect,USER,PASS);
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // フォームが送信された場合
    if (isset($_POST['confirm'])) {
        // 確認画面での「確認」ボタンが押された場合
        $title = $_POST['title'];
        $genre_id = $_POST['genre_id'];

        if (strlen($title) >= 1 && strlen($title) <= 30 && !empty($genre_id)) {
            $statement = $pdo->prepare('INSERT INTO thread(title, genre_id) VALUES(?, ?)');
            $statement->execute([$title, $genre_id]);
            // スレッドが正常に作成された場合、thread.php にリダイレクトする
            header("Location: thread.php?thread_id=" . $pdo->lastInsertId());
            exit(); // リダイレクト後にスクリプトの実行を停止する
        } else {
            echo '入力内容を確認してください。';
        }

    } elseif (isset($_POST['cancel'])) {
        // 確認画面での「キャンセル」ボタンが押された場合
        echo 'キャンセルされました';
        echo "<script>window.location.href = 'thread-write.php';</script>";
    }
}
$genre_query = $pdo->prepare('SELECT genre_name FROM genre WHERE genre_id = ?');
$genre_query->execute([$_POST['genre_id']]);
$genre_result = $genre_query->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <head>
    <title>新規スレッド確認画面</title>
    </head>
    <body>
<p>以下の内容でスレッドを作成しますか？</p>
<form action="" method="post">
    <label for="title">タイトル：</label>
    <p><?php echo $_POST['title']; ?></p>

    <label for="genre_id">ジャンル：</label>
    <p><?php echo $genre_result['genre_name']; ?></p>
    
    <input type="hidden" name="title" value="<?php echo $_POST['title']; ?>">
    <input type="hidden" name="genre_id" value="<?php echo $_POST['genre_id']; ?>">
    <input type="submit" name="confirm" value="確認">
    <input type="submit" name="cancel" value="キャンセル">

</body>
</html>