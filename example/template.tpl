<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>FluxTE.php Example</title>
	</head>
	<body>
		<h2>Hello World</h2>
		<p>
			{if($test)}
			Test is assigned!
			{else}
			Test is not assigned!
			{/if}
		</p>
		<p>
			Content of out_test is: {$out_test}
		</p>
		<p>
			This is included: {include($file_test .tpl)}
		</p>
		<p>
			If without else: {if($test)}{$out_test}{/if}
		</p>
		<p>
			Each:
			{each($each_test)}
			<p>{$test}</p>
			{/each}
		</p>
	</body>
</html>