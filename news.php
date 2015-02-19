<?php
    require_once('./app/Config.php');
    require_once('./app/WPAdmin.php');
    if(!empty($_GET['item'])){
        $item = $_GET['item'];
    }
    else{
        exit();
    }
    $wp_admin = new WPAdmin();
    $post = $wp_admin->_get_blog_inside($item);
?>
<!DOCTYPE html>
<html lang="pt-br" class="no-js">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h1><?php echo $post['post_title']; ?></h1>
        <h2><?php echo $wp_admin->_format_date($post['post_date']); ?></h2>
        <p><?php echo $wp_admin->_get_blog_categories($post['ID']); ?></p>
        <p><?php echo $wp_admin->_get_blog_tags($post['ID']); ?></p>
        <p><?php echo $post['post_content']?></p>
        <p><?php echo "<a href=\"../index.php\">Voltar</a>";?></p>
	</body>
</html>