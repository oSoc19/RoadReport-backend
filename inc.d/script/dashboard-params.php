<?php
	if (!isset($_SESSION['logged'])||$_SESSION['logged']!==true)
		exit;
	if (isset($_SESSION['dash_token-params'])&&isset($_POST['token']))
	{
		if ($_SESSION['dash_token-params']==$_POST['token'])
		{
			if (isset($_POST['object']))
				API::setParam('email_object', $_POST['object']);
			if (isset($_POST['body']))
				API::setParam('email_body', $_POST['body']);
			echo '<p class="alert alert-success" role="alert">Content up-to-dated.</p>';
		}
		else
		{
			echo '<p class="alert alert-danger" role="alert">Expired token</p>';
		}
	}
	$_SESSION['dash_token-params'] = md5(uniqid());
	?>
	<form method="POST" class="form-group">
		<input type="hidden" name="token" value="<?=$_SESSION['dash_token-params']?>"/>
		<table>
		<tr>
			<th align="left" colspan="2"><h3>Report e-mail<button class="btn btn-success btn-icon" data-icon="save" style="float: right;">SAVE</button></h3></td>
		</tr>
		<tr>
			<th><label for="object">Object:</label></th>
			<td><input id="object" type="text" name="object" class="form-control" maxlength="128" value="<?=htmlspecialchars(API::getParam('email_object'))?>"/></td>
		</tr>
		<tr>
			<th valign="top"><label for="body">Body:</label></th>
			<td>
				<textarea id="body" name="body" class="form-control" aria-describedby="emailHelp" style="min-height: 300px;"><?=htmlspecialchars(API::getParam('email_body'))?></textarea>
				<small id="emailHelp" class="form-text text-muted">You can use the following tags %REPORT_ID%, %REPORT_PROBLEM%, %REPORT_COMMENT%, %REPORT_DATE%, %REPORT_PICTURE%, %LOCATION_ID%, %LOCATION_STREET%, %LOCATION_NUMBER%, %LOCATION_CITY%, %LOCATION_LONG%, %LOCATION_LAT% and %USER_EMAIL%.</small>
			</td>
		</tr>
		</table>
	</form>