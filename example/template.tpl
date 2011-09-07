<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>FluxTE.php Example</title>
	</head>
	<body>
		<h2>Hello World</h2>
		<p>
			{:test}
			Test is assigned!
			{!test}
			Test is not assigned!
			{/test}
		</p>
		<p>
			Content of out_test is: {$out_test}
		</p>
		<p>
			This is included: {&file_test}
		</p>
		<p>
			If without else: {:test}{$out_test}{/test}
		</p>
	</body>
</html>