<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8" />
    <title>FileManager</title>

	<script src="./assets/js/jquery.js"></script>
	<script src="./assets/js/jquery-ui.custom.js"></script>
	<script src="./assets/js/jquery.fancytree.js"></script>
	<script src="./assets/js/jquery.fancytree.table.js"></script>

	<link rel="stylesheet" href="assets/css/global.css"/>
	<link rel="stylesheet" href="assets/css/smoothness/jquery-ui-1.10.3.custom.min.css"/>
	<link rel="stylesheet" href="assets/css/fancytree/skin-win7/ui.fancytree.css"/>

	<script>
		var prefixURLWithCurrentHost = true ;
	</script>

</head>
<body>

	<div id="login">
		<input type="text" id="url-dir" value="http://localhost:8888/Pro/EPFL/FileManager/api/exec/917065194d/dir/?path=." />
		<input type="button" id="url-submit" value="Connect" />
	</div>

	<table id="treetable" class="table-fancytree">
		<!--
		<colgroup>
			<col width="*"></col>
			<col width="30px"></col>
			<col width="30px"></col>
			<col width="30px"></col>
			<col width="30px"></col>
			<col width="30px"></col>
			<col width="30px"></col>
			<col width="30px"></col>
		</colgroup>
		-->
		<thead>
		<tr>
			<th>Name</th>
			<th>Write</th>
			<th>Read</th>
			<th>Delete</th>
			<th>Dir</th>
			<th>DirRO</th>
			<th>MkDir</th>
			<th>Delete</th>
			<th>CreateFile</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		</tbody>
	</table>

	<script>

		var rootNode ;

		function displayLink(name,link){
			if(! link) return $('<span>',{title:"No "+name}).text('-').addClass('no-link');

			if(prefixURLWithCurrentHost)
				link = location.protocol + '//' + location.hostname + ':' + location.port + link ;

			var $link = $('<a>',{href:link}).text(name) ;
			$link.on('click',function(){
				$('<div>').html(
					$('<input>').val(decodeURIComponent(link))
				).dialog({
					title:name
				});
				return false ;
			});
			return $link ;
		}

		$('#url-submit').on('click',function(){

			var mainURL = $('#url-dir').val() ;
			console.warn(mainURL);

			// Initial call, to check data
			$.ajax({
				dataType: "json",
				url: mainURL,
			}).done(function(data){

				if(data.error){
					if(data.error) alert(data.error);
					return;
				}

				rootNode = data[0];

				$("#treetable").fancytree({
					source: {
						url: rootNode.dirURL
					},
					lazyload: function(e, data){

						var d = data.node.data
						var url = d.dirURL ;
						if(d.dirReadOnlyURL){
							url = d.dirReadOnlyURL ;
							console.log("READ ONLY");
						}

						data.result = {url: url};
					},
					extensions: ["table"],
					table: {
						indentation: 20,      // indent 20px per node level
						nodeColumnIdx: 0,     // render the node title into the 2nd column
						//checkboxColumnIdx: 0  // render the checkboxes into the 1st column
					},
					checkbox: false,
					renderColumns: function(e, data) {
						var node = data.node, offset= 0,
						$tdList = $(node.tr).find(">td");

						console.log(node.data);

						$tdList.eq(offset+1).html(displayLink("write",node.data.writeURL));
						$tdList.eq(offset+2).html(displayLink("read",node.data.readURL));
						$tdList.eq(offset+3).html(displayLink("delete",node.data.deleteURL));
						$tdList.eq(offset+4).html(displayLink("dir",node.data.dirURL));
						$tdList.eq(offset+5).html(displayLink("dir (read only)",node.data.dirReadOnlyURL));
						$tdList.eq(offset+6).html(displayLink("mkdir",node.data.mkdirURL));
						$tdList.eq(offset+7).html(displayLink("rmdir",node.data.rmdirURL));
						$tdList.eq(offset+8).html(displayLink("create",node.data.createURL));

					}

				});

			}).error(function(data){
				var json = $.parseJSON(data.responseText);
				alert(json.error);
			});
		});
	</script>

</body>
</html>