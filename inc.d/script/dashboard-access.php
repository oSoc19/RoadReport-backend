<?php
	if (!isset($_SESSION['logged'])||$_SESSION['logged']!==true)
		exit;
	echo '<h3>API Notify Access</h3>';
	if (isset($_POST['action']))
	{
		if (isset($_POST['token'])&&isset($_SESSION['dash_token-access'])&&
			$_POST['token'] == $_SESSION['dash_token-access'])
		{
			if (isset($_POST['ip_addr']))
			{
				switch ($_POST['action']) {
					case 'add':
						if ($dash->addAllowedIP($_POST['ip_addr']))
							echo '<div class="alert alert-success" role="alert">operation successfully completed</div>';
						else
							echo '<div class="alert alert-danger" role="alert">Cannot add this ip address</div>';
						break;
					case 'remove':
						if ($dash->removeAllowedIP($_POST['ip_addr']))
							echo '<div class="alert alert-success" role="alert">operation successfully completed</div>';
						else
							echo '<div class="alert alert-danger" role="alert">Cannot remove this ip address</div>';
						break;
					
					default:
						echo '<div class="alert alert-danger" role="alert">Unkwon action</div>';
						break;
				}
			}
			else
			{
				echo '<div class="alert alert-danger" role="alert">No IP address provided</div>';
			}
		}
		else
		{
			echo '<div class="alert alert-danger" role="alert">Form expired</div>';
		}
	}
	$_SESSION['dash_token-access'] = md5(uniqid());
	?>
<form method="POST">
	<div class="form-group">
		<label for="ip-add">IPv4 to added:</label>
		<div style="display: flex;">
			<input id="ip-add" type="text" name="ip_addr" class="form-control" pattern="^((25[0-5]|2[0-4][0-9]|1?[0-9]{1,2})\.){3}(25[0-5]|2[0-4][0-9]|1?[0-9]{1,2})$" required="" />
			<input type="hidden" name="action" value="add" />
			<input type="hidden" name="token" value="<?=$_SESSION['dash_token-access']?>"/>
			<button class="btn btn-success btn-icon" data-icon="add_circle_outline">Add</button>
		</div>
	</div>
</form>
<table class="table">
<thead>
	<tr>
		<th scope="col">IPv4 Address allowed</th>
		<th scope="col">Action</th>
	</tr>
</thead>
<tbody>
<?php
	foreach ($dash->getAllowedIPList() as $ip)
	{
		$long = ip2long($ip);
		echo <<<IP
		<tr>
			<td>{$ip}</td>
			<td>
				<form class="table-action" method="POST">
					<input type="hidden" name="token" value="{$_SESSION['dash_token-access']}" />
					<input type="hidden" name="action" value="remove" />
					<input type="hidden" name="ip_addr" value="{$ip}" />
					<!--<input id="ip-{$long}" type="submit" value="remove_circle_outline" class="material-icons" />
					<label for="ip-{$long}">Remove</label>-->
					<button class="btn-text btn-icon" data-icon="remove_circle_outline">Remove</button>
				</form>
			</td>
		</tr>
IP;
	}
	?>
</tbody>
</table>