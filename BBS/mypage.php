<?php

$dataFile = 'bbs.dat';

//CSRF対策

session_start();

function setToken(){
  $token = sha1(uniqid(mt_rand(), true));
  $_SESSION['token'] = $token;
}

function checkToken(){
  if(empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])){
    echo "不正なPOSTが行われました！";
    exit;
  }
}

function h($s){
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' &&
  isset($_POST['message']) &&
  isset($_POST['user'])){

  checkToken();

  $message = trim($_POST['message']);
  $user = trim($_POST['user']);

  if($message !== ''){

    $user = ($user === '')? '名無しさん' : $user;

    $message = str_replace("\t", ' ', $message);
    $user = str_replace("\t", ' ', $user);

    $postedAt = date('Y-m-d H:i:s');


    $newData = $message . "\t" . $user . "\t" . $postedAt. "\n";

    $fp = fopen($dataFile, 'a');
    fwrite($fp, $newData);
    fclose($fp);
  }
}else{
  setToken();
}

$posts = file($dataFile, FILE_IGNORE_NEW_LINES);

$posts = array_reverse($posts);
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title>BBS</title>
    <link rel="stylesheet" href="mypage.css">
    <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  </head>

  <body>
    <div class="bg-image">
      <div class="bg-subimage">
        <header class="header-width">
          <h1><a href="mypage.php">BBS</a></h1>
          <nav id="top-nav">
            <ul>
              <li><a href="#list">投稿一覧</a></li>
              <li><a href="logout.php">ログアウト</a></li>
              <li><a href="contact.php">CONTACT</a></li>
            </ul>
          </nav>
        </header>

        <div class="main">
          <div class="main-contents">
            <form action="" method="post">
              <p class="user">user<br /></p>
              <textarea name="user" cols="50" rows="1"></textarea><br />
              <p class="message">message<br /></p>
              <textarea name="message" cols="50" rows="4"></textarea><br />
              <input type="submit" value="投稿" class="submit">
              <input type="hidden"  name="token" value="<?php echo h($_SESSION['token']); ?>">
            </form>
          </div>
        <div class="sub-contents">
          <h2>投稿一覧（<?php echo count($posts); ?>件）</h2>
            <div class="list">
              <ul>
                <?php if(count($posts)) : ?>
                  <?php foreach($posts as $post) : ?>
                  <?php list($message, $user, $postedAt) = explode("\t", $post); ?>

                    <li><?php echo h($message); ?> (<?php echo h($user); ?>) - <?php echo h($postedAt); ?></li>

                  <?php endforeach; ?>
                <?php else : ?>
                  <li>まだ投稿はありません。</li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>
        <footer>
          ©︎BBS All Rights Reserved.
        </footer>
      </div>
    </div>
  </body>
</html>
