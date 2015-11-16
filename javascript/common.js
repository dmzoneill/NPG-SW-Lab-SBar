var usedtable;

$.fn.exists = function () 
{
  return this.length !== 0;
}

$( document ).ready(function() 
{
  $( "#amountSpinner" ).spinner(
  {
    min: 1,
    max:100
  });
  
  $( "#copyButton" ).button();
  var client = new ZeroClipboard( $( "#copyButton" ) );
  
  client.on( 'ready', function(event) 
  {
    client.on( 'copy', function(event) 
    {
      var data = "";
      
      $('#newBarcodes td').each(function() 
      {
        data += $(this).html() + "\n"; 
      });			
      
      event.clipboardData.setData('text/plain', data );
    } );
  } );
  
  $( "#createButton" ).button();	

  if( $("#usedDutBarcodes").exists() == true )
  {  
    usedtable = $( "#usedDutBarcodes" ).DataTable({
      "processing": true,
      "serverSide": true,
      "ordering": false,
      "searching": false,
      "ajax": "index.php?DutGetBarcodes=true"
    });      
    
    $( "#createButton" ).click( function(){ createDutSerials() } );  
  }
  else
  {  
    usedtable = $( "#usedHpvipBarcodes" ).DataTable({
      "processing": true,
      "serverSide": true,
      "ordering": false,
      "searching": false,
      "ajax": "index.php?HVPIPGetBarcodes=true"
    });	
    
    $( "#createButton" ).click( function(){ createHvpip() } );	
  }  		
});

function createHvpip()
{
  var createAmount = $( "#amountSpinner" ).spinner( "value" );
  var specNew = $( "#specNew" ).val() + "";
        
  if( createAmount < 1 )
  {
    return;
  }
  
  $.get( "index.php", { HVPIPBarcodeCreateAmount: createAmount, HVPIPBarcodeSpecno: specNew } ).done(function( data ) 
  {		
    $( "#specNew" ).val("");			
    $( "#newBody" ).html( data );		
    $( "#newBarcodes" ).show();			
    
    if ( $.fn.dataTable.isDataTable( '#newBarcodes' ) ) 
    {
      table = $('#newBarcodes').DataTable();
    }
    else 
    {
      table = $('#newBarcodes').DataTable( 
      {
        "paging": false,
        "ordering": false,
        "info": false,
        "searching" : false
      } );
    }
             
    usedtable.ajax.reload();
  });	
}

function createDutSerials()
{
  var createAmount = $( "#amountSpinner" ).spinner( "value" );
			
  if( createAmount < 1 )
  {
    return;
  }
  
  $.get( "index.php", { DutBarcodeCreateAmount: createAmount } ).done(function( data ) 
  {
    $( "#newBody" ).html( data );		
    $( "#newBarcodes" ).show();			
    
    if ( $.fn.dataTable.isDataTable( '#newBarcodes' ) ) 
    {
      table = $('#newBarcodes').DataTable();
    }
    else 
    {
      table = $('#newBarcodes').DataTable( 
      {
        "paging": false,
        "ordering": false,
        "info": false,
        "searching" : false
      } );
    }
             
    usedtable.ajax.reload();
  });
}