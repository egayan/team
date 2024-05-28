<?php
session_start();
require('db-connect.php');

// データベースに接続
$pdo = new PDO($connect, USER, PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// スレッドのIDを取得
$thread_id = $_GET['thread_id'] ?? null;

$thread_title = '';
if ($thread_id) {
    $stmt = $pdo->prepare('SELECT title FROM thread WHERE thread_id = ?');
    $stmt->execute([$thread_id]);
    $thread = $stmt->fetch(PDO::FETCH_ASSOC);
    $thread_title = $thread['title'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (!empty($_POST['post']) && strlen($_POST['post']) <= 200) {
    // 新しい投稿をデータベースに挿入
    $stmt = $pdo->prepare('INSERT INTO post (thread_id, post, date) VALUES (?,  ?, NOW())');
    $stmt->execute([$thread_id,$_POST['post']]);
}else if(strlen($_POST['post']) > 200){
echo '<script>alert("200文字以内で書いてください")</script>';
}else{
echo '<script>alert("入力してください")</script>';
}

if($_POST['post'] == $ngword['ngword_content']){
    echo '<script>alert("NGワードが含まれています")</script>';

}
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>スレッド: <?php echo htmlspecialchars($thread_title, ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body>
<h1><?php echo htmlspecialchars($thread_title, ENT_QUOTES, 'UTF-8'); ?></h1>
<?php
try {
    // スレッドに関連する投稿を取得
    $stmt = $pdo->prepare("SELECT * FROM post WHERE thread_id = ? ORDER BY date ASC");
    $stmt->execute([$thread_id]);
    while ($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div>';
        echo '投稿: ' . nl2br(htmlspecialchars($post['post'], ENT_QUOTES, 'UTF-8')) . '<br>';
        echo '投稿日時: ' . htmlspecialchars($post['date'], ENT_QUOTES, 'UTF-8') . '<br>';
        echo '</div><hr>';
    }
} catch (PDOException $e) {
    echo 'エラー: ' . $e->getMessage();
}
?>
<form action="" method="POST">
    <textarea name="post" cols="50" rows="10" placeholder="ここに投稿を入力してください"></textarea><br>
    <button type="submit">投稿する</button>
</form>
<a href="a.php">戻る</a>
</body>
</html>
