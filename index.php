<?php
	require_once('./app/Config.php');
	require_once('./app/WPAdmin.php');
	$num_posts = 5;
	$posts = $wp_admin->_get_posts(0, $num_posts, 100);
?>
<!DOCTYPE html>
<html lang="pt-br" class="no-js">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Posts From Wordpress</title>
	</head>
	<body>
	<?php
		for($i=0; $i<$num_posts; $i++){
			echo "<h1>".$posts[$i]['title']."</h1>";
			echo "<a href=\"" . $posts[$i]['perma'] . "\">Veja a notícia no blog</a>";
			echo "<br/>";
			echo "<a href=\"news/" . $posts[$i]['name'] . "\">Veja a notícia no site</a>";
			echo "<br/>";
			if(!empty($posts[$i]['picture']))
				echo "<img src=\"".$posts[$i]['picture']."\"/>";
			echo "<p>".$posts[$i]['abstract']."</p>";
			echo "<small>".$posts[$i]['date']."</small>";
			echo "<hr>";
		}
	?>
	</body>
</html>