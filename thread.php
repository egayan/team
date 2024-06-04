<?php
session_start(); // セッションの開始

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

// NGワードを取得
$stmt = $pdo->query('SELECT ngword_content FROM ngword');
$ngwords = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 友達追加の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['friend_id'])) {
    $opponent_id = $_POST['friend_id']; // ポップアップから送信された相手のIDを取得
    $user_id = $_SESSION['customer']['id'] ?? null; // ログインしているユーザーのIDを取得

    if ($user_id!= null) {
        // すでに友達関係が存在するか確認
        $stmt = $pdo->prepare('SELECT * FROM friend WHERE (client_id = ? AND opponent_id = ?) OR (client_id = ? AND opponent_id = ?)');
        $stmt->execute([$user_id, $opponent_id, $opponent_id, $user_id]);
        if ($stmt->fetch()) {
            echo '<script>alert("既に友達です。");</script>';
        } else {
            // クライアントIDがデータベースに存在することを確認
            $stmt = $pdo->prepare('SELECT client_id FROM client WHERE client_id = ?');
            $stmt->execute([$user_id]);
            if ($stmt->fetch()) {
                $stmt = $pdo->prepare('SELECT client_id FROM client WHERE client_id = ?');
                $stmt->execute([$opponent_id]);
                if ($stmt->fetch()) {
                    $stmt = $pdo->prepare('INSERT INTO friend (friend_id, client_id, opponent_id) VALUES (null, ?, ?)');
                    $stmt->execute([$user_id, $opponent_id]);
                    echo '<script>alert("友達を追加しました！");</script>';
                } else {
                    echo '<script>alert("相手のIDが存在しません。");</script>';
                }
            } else {
                echo '<script>alert("ユーザーIDが存在しません。");</script>';
            }
        }
    } else {
        echo '<script>alert("ログインしていません。");</script>';
    }
}

// 投稿の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post'])) {
    if (!empty($_POST['post']) && strlen($_POST['post']) <= 200) {
        if (isset($_SESSION['login']['id'])) {
            $contains_ngword = false;
            foreach ($ngwords as $ngword) {
                if (strpos($_POST['post'], $ngword) !== false) {
                    $contains_ngword = true;
                    break;
                }
            }
            if (!$contains_ngword) {
                $client_id = $_SESSION['customer']['id'];
                $stmt = $pdo->prepare('INSERT INTO post (thread_id, post, client_id, date) VALUES (?, ?, ?, NOW())');
                $stmt->execute([$thread_id, $_POST['post'], $client_id]);
            } else {
                echo '<script>alert("NGワードが含まれています")</script>';
            }
        } else {
            echo '<script>alert("ログインしてください")</script>';
        }
    } else if (strlen($_POST['post']) > 200) {
        echo '<script>alert("200文字以内で書いてください")</script>';
    } else {
        echo '<script>alert("入力してください")</script>';
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
    <link rel="stylesheet" href="thread.css">
    <title>スレッド: <?php echo htmlspecialchars($thread_title, ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body>
<h1><?php echo htmlspecialchars($thread_title, ENT_QUOTES, 'UTF-8'); ?></h1>
<?php
try {
    // スレッドに関連する投稿を取得
    $stmt = $pdo->prepare("SELECT post.*, client.name, client.client_id FROM post LEFT JOIN client ON post.client_id = client.client_id WHERE post.thread_id = ? ORDER BY post.date ASC");
    $stmt->execute([$thread_id]);
    while ($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div>
            名前: <a href="#" class="popupLink" data-client-id="<?php echo htmlspecialchars($post['client_id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8'); ?></a>
            通報する:<a href="#" class="popupLink" data-client-id="<?php echo htmlspecialchars($post['client_id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8'); ?></a><br>
            投稿: <?php echo nl2br(htmlspecialchars($post['post'], ENT_QUOTES, 'UTF-8')); ?><br>
            投稿日時: <?php echo htmlspecialchars($post['date'], ENT_QUOTES, 'UTF-8'); ?><br>
        </div>
        <hr>
        <?php
    }
    ?>
    <form action="" method="POST">
        <textarea name="post" cols="50" rows="10" placeholder="ここに投稿を入力してください"></textarea><br>
        <button type="submit">投稿する</button>
    </form>
    <a href="Top.php">戻る</a>
<?php
} catch (PDOException $e) {
    echo 'エラー: ' . $e->getMessage();
}
?>

<div class="overlay" id="overlay"></div>
<div class="popup" id="popup">
    <p>友達追加しますか？</p>
    <button onclick="addFriend()">追加する</button>
    <button onclick="closePopup()">キャンセル</button>
    <!-- 友達追加フォーム -->
    <form id="addFriendForm" action="" method="POST">
        <input type="hidden" name="friend_id" id="friendIdField">
    </form>
</div>

<script>
    // ポップアップを開く
    function openPopup(clientId) {
        document.getElementById("overlay").style.display = "block";
        document.getElementById("popup").style.display = "block";
        document.getElementById("popup").setAttribute('data-client-id', clientId);
    }

    // ポップアップを閉じる
    function closePopup() {
        document.getElementById("overlay").style.display ="none";
        document.getElementById("popup").style.display ="none";
    }

    // 友達追加ボタンをクリックしたときの処理
    function addFriend() {
        var clientId = document.getElementById("popup").getAttribute('data-client-id');
        document.getElementById("friendIdField").value = clientId;
        document.getElementById("addFriendForm").submit();
    }

    // ポップアップを表示するリンクをクリックしたときの処理
    var popupLinks = document.querySelectorAll('.popupLink');
    popupLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            var clientId = this.getAttribute('data-client-id');
            openPopup(clientId);
        });
    });
</script>

</body>
</html>
