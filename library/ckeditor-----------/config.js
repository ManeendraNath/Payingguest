/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
        
//        config.toolbarGroups = [
//		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
//		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
//		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
//		{ name: 'forms', groups: [ 'forms' ] },
//		'/',
//		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
//		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
//		{ name: 'links', groups: [ 'links' ] },
//		{ name: 'insert', groups: [ 'insert' ] },
//		'/',
//		{ name: 'styles', groups: [ 'styles' ] },
//		{ name: 'colors', groups: [ 'colors' ] },
//		{ name: 'tools', groups: [ 'tools' ] },
//		{ name: 'others', groups: [ 'others' ] },
//		{ name: 'about', groups: [ 'about' ] }
//	];

//config.extraPlugins = 'wordcount,notification';

//Setup for advance editor
config.toolbar = [
		{ name: 'document', items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates' ] },
		{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
		{ name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
		{ name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
		'/',
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
		{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
		{ name: 'insert', items: [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
		'/',
		{ name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
		{ name: 'about', items: [ 'About' ] }
	];


/***************************************************/
//Disallowing entire elements.
config.allowedContent = 'h1 h2 h3 p';
config.disallowedContent = 'h2 h3';
// Input:    <h1>Foo</h1><h2>Bar</h2><h3>Bom</h3>
// Filtered: <h1>Foo</h1><p>Bar</p><p>Bom</p>

/***************************************************/
//Disallowing attributes, classes, and styles.
config.allowedContent = 'p[*]{*}(foo,bar)';
config.disallowedContent = 'p[on*](foo)';
// Input:    <p>Foo</p><p onclick="..." data-foo="1" class="foo bar">Bar</p>
// Filtered: <p>Foo</p><p data-foo="1" class="bar">Bar</p>

/***************************************************/
//Disallowing a required property.
config.allowedContent = 'p; img[!src,alt]';
config.disallowedContent = 'img[src]';
// Input:    <p><img src="../assets/img/..." alt="..."/></p>
// Filtered: <p/>


/***************************************************/
//Tweaking automatically allowed content.
// Enabled plugins: image and table.
config.disallowedContent = 'img{border*,margin*}; table[border]{*}';


/***************************************************/
//The below code sample will allow everything except for the <script> elements and attributes starting from 'on'.
config.allowedContent = {
    $1: {
        // Use the ability to specify elements as an object.
        elements: CKEDITOR.dtd,
        attributes: true,
        styles: true,
        classes: true
    }
};
config.disallowedContent = 'script; *[on*]';

/***************************************************/
config.extraPlugins = 'wordcount,notification';
config.wordcount = {

    // Whether or not you want to show the Paragraphs Count
    showParagraphs: false,

    // Whether or not you want to show the Word Count
    showWordCount: true,

    // Whether or not you want to show the Char Count
    showCharCount: true,

    // Whether or not you want to count Spaces as Chars
    countSpacesAsChars: true,

    // Whether or not to include Html chars in the Char Count
    countHTML: false,
    
    // Whether or not to include Line Breaks in the Char Count
    countLineBreaks: true,

    // Maximum allowed Word Count, -1 is default for unlimited
    maxWordCount: -1,

    // Maximum allowed Char Count, -1 is default for unlimited
   // maxCharCount: -1,
    maxCharCount: 10000,
    
    // Maximum allowed Paragraphs Count, -1 is default for unlimited
    maxParagraphs: -1,

    // How long to show the 'paste' warning, 0 is default for not auto-closing the notification
    pasteWarningDuration: 0,
    

    // Add filter to add or remove element before counting (see CKEDITOR.htmlParser.filter), Default value : null (no filter)
    filter: new CKEDITOR.htmlParser.filter({
        elements: {
            div: function( element ) {
                if(element.attributes.class == 'mediaembed') {
                    return false;
                }
            }
        }
    })
};
};
