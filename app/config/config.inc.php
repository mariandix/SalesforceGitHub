<?php 

// rqa

define("CLIENT_ID", "3MVG9od6vNol.eBgxIiNM0JqZp_YuXM5fxbYJ5_UJvbU78w98EESnSRTODTMGhM51aECTJNya0ok0flaqDuzi");
define("CLIENT_SECRET", "3577859520326194529");
define("LOGIN_URI", "https://test.salesforce.com");
define("PROXY_URI", "https://10.222.0.2:3128"); 
define("INCLUDE_PROXY", false); // true or false 
define('USERNAME', 'hmalhotra@meinservice.ext.dpdhl.rqa');
define('PASSWORD', 'P@ss0102VEIoEkXNzTpgM9fz2GRupAya');

define('DEPLOYMENT_ID', '572260000008OQT');
define('BUTTON_ID', '573260000008Oh0');
define('ORG_ID', '00D260000009FVj');


// dev
/*define("CLIENT_ID", "3MVG954MqIw6FnnM4PE4C_n5rf0LaljT_OSDyZOy7ZnuYv.ij6XWqYIxoW9ttOqeWiQ_K4sWuk9xCTbxak78b");
define("CLIENT_SECRET", "4557138230826258012");
define("LOGIN_URI", "https://test.salesforce.com");  
define('USERNAME', 'hmalhotra@dpdhl.chatbot.dev');
define('PASSWORD', 'P@ss0102nHI6woYcRV7ZfFknsYQJsxVLQ');

define('DEPLOYMENT_ID', '5728E0000008OP1');
define('BUTTON_ID', '5738E0000008OSU');
define('ORG_ID', '00D8E000000D20l');
*/

define('COGNESYS_URL', 'https://lhZepTho1M0xnFVznOsT:GRi78QOuyeHpFh0bC5BA@api.cognesys.de:5679/api/v1');
//define('COGNESYS_URL', 'https://trh78b4563vg456456:nuk3258nxq2q3c524@api.cognesys.de:5679/prototype');
//define('COGNESYS_URL', 'https://nuk3258nxq2q3c524:trh78b4563vg456456@78.137.97.103:4444/prototype');

define('LIVEAGENT_REST_URL', 'https://d.la1-c2cs-lon.salesforceliveagent.com/chat/rest');

define('LIVEAGENT_CHECK_COUNT', 2);

include(dirname(__FILE__) . '/../lib/connector.php');

?>