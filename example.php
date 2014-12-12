<?php
try
{
	require_once(__DIR__.'/node_list.php');
	require_once(__DIR__.'/node.php');

	// get all you nodes
	$result = myRouterList::getAll();

	// no that we have something - compule the data
	$nodeList = new nodeList();

	// set some information about the community
	$nodeList->setCommunityName('FreifunkEmskirchen');
	$nodeList->setWebsite('http://www.freifunk-emskirchen.de');
	$nodeList->setCommunityFile('https://raw.githubusercontent.com/ffansbach/community-files/master/emskirchen.json');

	// add all the nodes
	foreach($result as $resultNode)
	{
		// prepare new node with id and name
		$node = new node($resultNode['id'], $resultNode['hostname']);

		// nodetype - AP, gateway, switch ...
		$node->setType('AccessPoint');

		// optional - details about that node
		$node->setHref('https://netmon.freifunk-emskirchen.de/router.php?router_id='.$resultNode['id']);

		$node->setStatus(
			($resultNode['status'] == 'online'),
			$resultNode['client_count'],
			$resultNode['last_seen']
		);

		// optional
		$node->setUserId($resultNode['u_id']);
		$nodeList->addPerson(
			$resultNode['u_id'],
			$resultNode['nickname'],
			'https://netmon.freifunk-emskirchen.de/user.php?user_id='.$resultNode['u_id']
		);

		// required for the map
		$node->setGeo(
			$resultNode['latitude'],
			$resultNode['longitude']
		);

		// add this node to our list
		$nodeList->addNode($node->getNode());
	}

	try
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60*15)));
		header('Content-type: application/json');
		echo json_encode($nodeList->getList(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	}
	catch (Exception $e)
	{
		echo 'Unable to create nodelist: ',  $e->getMessage(), "\n";
	}

}
catch (Exception $e)
{
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
