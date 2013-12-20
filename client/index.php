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

	<div id="test-views">
		<div id="test-views-result">Ready!</div>

		<textarea id="content-test" rows="5">{
	"test":"it works",
	"test2":{
		"lebel2":"nooo",
		"array":[1,2,3,4,5]
	},
	"test3":false
}</textarea>

		<input type="text" id="input-getview" value="/Pro/EPFL/FileManager/api/exec/2d8d7212e3/getview/?path=.&name=138727445202" />
		<input type="button" id="submit-getview" value="get JSON">

		<input type="text" id="input-setview" value="/Pro/EPFL/FileManager/api/exec/6ae73e98fa/setviewdata/?path=." />
		<input type="button" id="submit-setview" value="set data (with test object)">
	</div>

	<div id="login">
		<input type="text" id="url-dir" value="http://localhost:8888/Pro/EPFL/FileManager/api/exec/917065194d/dir/?path=." />
		<input type="button" id="url-submit" value="Connect" />
	</div>

	<table id="treetable" class="table-fancytree">
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
			<th>SetView Data</th>
			<th>SetData Data</th>
			<th>getViewBranches</th>
			<th>getViewBranches RO</th>
			<th>getDataBranches</th>
			<th>getDataBranches RO</th>
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
			<td></td>
			<td></td>
			<td></td>
		</tr>
		</tbody>
	</table>

	<script>

		$(function(){

			var rootNode ;
			var $results = $('#test-views-result') ;
			var classError = 'view-error' ;

			function getTest(){
				try{
					return $.parseJSON($('#content-test').val());
				}catch (err){
					alert("Cannot parse JSON: "+err);
					return false ;
				}
			}

			function displayLink(name,link){
				if(! link) return $('<span>',{title:"No "+name}).text('-').addClass('no-link');

				if(prefixURLWithCurrentHost)
					link = window.location.origin + link ;

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

			$('#submit-getview').on('click',function(){
				var a = $('#input-getview').val() ;
				$results.removeClass(classError);
				$.getJSON(a,function(a){
					$results.html(JSON.stringify(a));
				}).error(function(a){
						$results.addClass(classError);
						$results.html(a.responseText);
					});
			});

			$('#submit-setview').on('click',function(){
				var a = $('#input-setview').val() ;
				var data = getTest() ;
				if(! data) return ;
				$results.removeClass(classError);
				$.ajax({
					url:a,
					data:data,
					type:'post'
				}).success(function(a){
					$results.html(JSON.stringify(a));
				}).error(function(a){
						$results.addClass(classError);
						$results.html(a.responseText);
					}).complete(function(a){
						console.log(a.responseText);
					});
			});

			$('#url-submit').on('click',function(){

				var mainURL = $('#url-dir').val() ;
				console.warn(mainURL);

				// Initial call, to check data
				$.ajax({
					dataType: "json",
					url: mainURL
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
							if(! url && d.dirReadOnlyURL){
								url = d.dirReadOnlyURL ;
								console.log("READ ONLY");
							}
							data.result = {url: url};
						},
						extensions: ["table"],
						table: {
							indentation: 20,      // indent 20px per node level
							nodeColumnIdx: 0     // render the node title into the 2nd column
						},
						checkbox: false,
						renderColumns: function(e, data) {
							var node = data.node, offset= 0,
							$tdList = $(node.tr).find(">td");

							console.log(node.data);

							$tdList.eq(offset+ 1).html(displayLink("write",node.data.writeURL));
							$tdList.eq(offset+ 2).html(displayLink("read",node.data.readURL));
							$tdList.eq(offset+ 3).html(displayLink("delete",node.data.deleteURL));
							$tdList.eq(offset+ 4).html(displayLink("dir",node.data.dirURL));
							$tdList.eq(offset+ 5).html(displayLink("dir (read only)",node.data.dirReadOnlyURL));
							$tdList.eq(offset+ 6).html(displayLink("mkdir",node.data.mkdirURL));
							$tdList.eq(offset+ 7).html(displayLink("rmdir",node.data.rmdirURL));
							$tdList.eq(offset+ 8).html(displayLink("create",node.data.createURL));
							$tdList.eq(offset+ 9).html(displayLink("setviewdata",node.data.setViewDataURL));
							$tdList.eq(offset+10).html(displayLink("setdatadata",node.data.setDataDataURL));
							$tdList.eq(offset+11).html(displayLink("getviewbranches",node.data.getViewBranchesURL));
							$tdList.eq(offset+12).html(displayLink("getviewbranches (read only)",node.data.getViewBranchesReadOnlyURL));
							$tdList.eq(offset+13).html(displayLink("getdatabranches",node.data.getDataBranchesURL));
							$tdList.eq(offset+14).html(displayLink("getdatabranches (read only)",node.data.getDataBranchesReadOnlyURL));

						}

					});

				}).error(function(data){
					var json = $.parseJSON(data.responseText);
					alert(json.error);
				});
			});

		});

	</script>

</body>
</html>