
function add_field(forWhat) {
	var holder = document.getElementById( 'field_box' );
	var theId = document.getElementById( 'field_next' ).value;

	var newDiv = document.createElement( 'tr' );
	newDiv.setAttribute( 'id', 'field_div' + theId );
	newDiv.setAttribute( 'class', 'sortable' );

	var $select = jQuery("<select>", {name: 'bps_options[field_name][' + theId + ']', id: 'field_name' + theId, style:'width:28%;'});
	var $option = jQuery("<option>", {text: bps_strings.field, value: 0});
	$option.appendTo($select);

	jQuery.each( bps_groups, function( i, optgroups ) {
		jQuery.each( optgroups, function( groupName, options ) {
			var $optgroup = jQuery( "<optgroup>", {label: groupName} );
			$optgroup.appendTo( $select );

			jQuery.each( options, function( j, option ) {
				var $option = jQuery( "<option>", {text: option.name, value: option.id} );
				$option.appendTo( $optgroup );
			});
		});
	});
	var tableCell = document.createElement( 'td' );
	tableCell.appendChild( $select );
	$select = tableCell;


	var fieldL = document.createElement( 'input' );
	fieldL.setAttribute( 'type', 'text' );
	fieldL.setAttribute( 'name', 'bps_options[field_label][' + theId + ']' );
	fieldL.setAttribute( 'id', 'field_label' + theId );
	fieldL.setAttribute( 'placeholder', bps_strings.label );
	var tableCell = document.createElement( 'td' );
	tableCell.appendChild( fieldL );
	fieldL = tableCell;

	var fieldD = document.createElement( 'input' );
	fieldD.setAttribute( 'type', 'text' );
	fieldD.setAttribute( 'name', 'bps_options[field_desc][' + theId + ']' );
	fieldD.setAttribute( 'id', 'field_desc' + theId );
	fieldD.setAttribute( 'placeholder', bps_strings.description );
	var tableCell = document.createElement( 'td' );
	tableCell.appendChild( fieldD );
	fieldD = tableCell;

	var range = document.createElement( 'input' );
	range.setAttribute( 'type', 'checkbox' );
	range.setAttribute( 'name', 'bps_options[field_range][' + theId + ']' );
	range.setAttribute( 'id', 'field_range' + theId );
	range.setAttribute( 'value', theId );
	var tableCell = document.createElement( 'td' );
	tableCell.appendChild( range );
	range = tableCell;

	var $memberTypeSelect = jQuery("<select>", {name: 'bps_options[field_member_type][' + theId + ']', id: 'field_member_type' + theId, style:'width:28%;'});
	var $memberTypeOption = jQuery("<option>", {text: bps_strings.member_type, value: 0});
	$memberTypeOption.appendTo($memberTypeSelect);
	jQuery.each( bps_member_types, function( value, text ) {
		var $option = jQuery( "<option>", {text: text, value: value} );
		console.log(text);
		$option.appendTo( $memberTypeSelect );
	});
	var tableCell = document.createElement( 'td' );
	tableCell.appendChild( $memberTypeSelect[0] );
	$memberTypeSelect = tableCell;

	var toDelete = document.createElement( 'a' );
	toDelete.setAttribute( 'href', "javascript:hide('field_div" + theId + "')" );
	toDelete.setAttribute( 'class', 'delete' );
	toDelete.appendChild( document.createTextNode( '[x]' ) );
	var tableCell = document.createElement( 'td' );
	tableCell.appendChild( toDelete );
	toDelete = tableCell;

	holder.appendChild( newDiv );
	$select.appendTo("#field_div" + theId);
	newDiv.appendChild( fieldL );
	newDiv.appendChild( fieldD );
	newDiv.appendChild( range );
	newDiv.appendChild( $memberTypeSelect );
	newDiv.appendChild( toDelete );

	enableSortableFieldOptions();
	document.getElementById( 'field_name' + theId ).focus();
	document.getElementById( 'field_next' ).value = ++theId;
}

function hide( id ) {
	var element = document.getElementById( id );
	element.parentNode.removeChild( element );
}

function enableSortableFieldOptions() {
	jQuery( '.field_box' ).sortable( {
		items: 'p.sortable',
		tolerance: 'pointer',
		axis: 'y',
		handle: 'span'
	});

	jQuery( '.sortable span' ).css( 'cursor', 'move' );
}

jQuery( document ).ready( function() {
	enableSortableFieldOptions();
});
