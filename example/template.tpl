<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>Vera.php Example</title>
	</head>
	<body>
		<h2>Vera Template!</h2>
		<h3>If</h3>
		<p>
			{if($test)}Test is assigned!{/if}
		</p>
		<h3>If/Else</h3>
		<p>
			{if($test)}
			Test is assigned!
			{else}
			Test is not assigned!
			{/if}
		</p>
		<h3>Variable</h3>
		<p>
			{$test}
		</p>
		<h3>Include</h3>
		<p>
			This is included: {include($include_test'.tpl')}
		</p>
		<h3>Each</h3>
		<p>
			Each:
			{each($each_test)}
			<p>{$var} {if($var2)}{$var2}{/if}</p>
			{/each}
		</p>
		<h3>Array Variable</h3>
		<p>
			{$array['test']}<br/>
			{$array[0]}
		</p>
	</body>
</html>