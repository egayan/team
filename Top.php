<?php session_start(); ?>
<?php require 'header.php'; ?>
<?php require 'db_conect.php'; ?>
<?php
if(isset($_GET['gest'])){
?>
    <!-- メニュー -->
    <table align="center">
    
    <tr><td><div align="center">チャイニーズドラゴン</div></td></tr>
    <tr><td><div align="center">機能を利用するには<button><a href="login_input.php">ログイン画面へ戻る</a></button></div></td></tr>
    
    <?php
    $pdo = new PDO($connect,USER,PASS);
    $tr=0;
    $sql = $pdo->query('select * from thread');
    
    echo '<tr><td><div align="center">スレッド一覧</div></td></tr>';
    echo '<tr>';
    echo '<td>';
    echo '<div  align="left" >';
    foreach($sql as $row){
        
        echo '<a href="thread.php?thread_id=',$row['thread_id'],'">',$row['title'],'</a>';
        $tr++;
        if($tr==3){
        echo '</div>';
        echo '</td>';
        echo '</tr>';
        $tr=0;
        echo '<tr>';
        echo '<td>';
        echo '<div align="left">';
        }
    }
    echo '</div>';
    echo '</td>';
    echo '</tr>';
    echo '<tr><td><div align="center">';
    echo '<button><a href="*">ジャンル一覧へ</a></button>';
    echo '<button><a href="Popularity.php?gest=gest">人気スレッドへ</a></button></div></td></tr>';
    
    echo '<tr><td>';
    echo '<div align="center">';
    echo '<button><a href="*">使い方・注意</a></button></div></td></tr>';
    echo '</table>';
    ?>
<?php
}else{
if(isset($_POST['passward']) || isset($_POST['login'])){
    unset($_SESSION['customer']);
    $_SESSION['login']=[
        'id'=>0
    ];
if($_POST['password'] != null && $_POST['login'] != null){
$pdo = new PDO($connect,USER,PASS);
$sql = $pdo->prepare('select * from client where client_address=?');
$sql->execute([$_POST['login']]);
foreach($sql as $row){
    if($_POST["password"]==$row['password']){
    $_SESSION['customer']=[
        'id'=>$row['client_id'],'name'=>$row['name'],
        'password'=>$row['password'],'address'=>$row['client_address']
    ];
    }
}
if(isset($_SESSION['customer'])){
    echo 'いらっしゃいませ、',$_SESSION['customer']['name'],'さん。';
    $_SESSION['login']=[
        'id'=>1
    ];
}else{
    echo 'ログイン名またはパスワードが違います。';
    echo '<a href="login_input.php">ログインへ</a>';
}
}else{
    echo 'ログイン名またはパスワードを入力してください。';
    echo '<a href="login_input.php">ログインへ</a>';
}
}
if($_SESSION['login']['id']==1){

$freeze_check = new PDO($connect,USER,PASS);
$freeze_check = $pdo->prepare('select * from client where client_address=?');
$freeze_check ->execute([$_POST['login']]);
foreach($freeze_check as $row){
$check=$row['freeze'];
}
if($check == 1){
?>
<table align="center">
  <tr><td><div align="center">チャイニーズドラゴン
  <a href="login_input.php">ログアウト</a></div></td></tr>
  
  <tr><td><div align="center">
  <form action="Top_kensakukekka.php" method="post">
    <input type="text" placeholder="検索" name="kensaku" size="70" ><input type="submit" value="検索" size="35" >
  </form>
  </div></td></tr>

<?php
$pdo = new PDO($connect,USER,PASS);
$tr=0;
$sql = $pdo->query('select * from thread');
    echo '<tr><td><div align="center">スレッド一覧</div></td></tr>';
    echo '<tr>';
    echo '<td>';
    echo '<div align="left">';
    foreach($sql as $row){
        
        echo '<a href="thread.php?thread_id=',$row['thread_id'],'">',$row['title'],'</a>';
        $tr++;
        if($tr==3){
        echo '</div>';
        echo '</td>';
        echo '</tr>';
        $tr=0;
        echo '<tr>';
        echo '<td>';
        echo '<div align="left">';
        }
    }
    echo '</div>';
    echo '</td>';
    echo '</tr>';
    echo '<tr><td><div align="center"><button><a href="thread-write.php">新規スレッド書き込み画面へ</a></button>';
    echo '<button><a href="*">ジャンル一覧へ</a></button>';
    echo '<button><a href="Popularity.php">人気スレッドへ</a></button></div></td></tr>';
    
    echo '<tr><td><div align="center"><button><a href="*">個人チャット</a></button>';
    echo '<button><a href="mypage.php">マイページ</a></button>';
    echo '<button><a href="*">お問い合わせ</a></button>';
    echo '<button><a href="*">使い方・注意</a></button></div></td></tr>';
    echo '</table>';
?>
<?php 
    }else{
        echo '<h1>このアカウントは凍結されています</h1>';
    }
}
?>
<?php 
}
?>
<?php require 'footer.php'; ?> 