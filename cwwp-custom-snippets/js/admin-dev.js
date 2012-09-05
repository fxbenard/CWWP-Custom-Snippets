/**
 * Code With WP Custom Snippets jQuery.
 *
 * @since 1.0.0
 *
 * @package	CWWP_Custom_Snippets
 * @author	Thomas Griffin
 */
jQuery(document).ready(function($){

	/** Move the publish button below the wp_editor instance */
	$('#publishing-action').insertAfter('.wp-editor-wrap');
	$('#delete-action').wrapInner('<div class="submitbox" />').insertAfter('#publishing-action');
	$('#publishing-action .ajax-loading').insertAfter('#delete-action a');
	
	/** Initialize the code snippet editor from CodeMirror */
	var cwwp_code_editor = CodeMirror.fromTextArea(document.getElementById('cwwp-code-snippet'), {
		enterMode: 		'keep',
		indentUnit: 	4,
		indentWithTabs: true,
		lineNumbers: 	true,
		lineWrapping: 	true,
		matchBrackets: 	true,
		mode: 			'application/x-httpd-php-open',
		onBlur:			function(){cwwp_code_editor.save()},
		onFocus:		function(){cwwp_code_editor.save()},
		tabMode: 		'shift',
		theme:			'elegant'
	});
	$(cwwp_code_editor.getWrapperElement()).slideDown('normal', function(){
		cwwp_code_editor.refresh();
	});
	
	/** Make sure the code is saved before the submit event is fired */
	$('#publish').click(function(){
		cwwp_code_editor.save();
	});
	
});