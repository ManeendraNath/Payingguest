ClassicEditor
	.create( document.querySelector( '#editor1' ), {
		toolbar: [
			'heading', '|', 'bold', 'italic', 'link', 'unlink', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo'
		]
	} )
	.then( editor => {
		console.log( editor );
	} )
	.catch( error => {
		console.error( error );
	} );