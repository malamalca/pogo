$(document).ready(function() {
	$('#UserKind').change(function() {
		toggleUserKind();
	});
	
	function toggleUserKind() {
		console.log($('#UserKind').val());
		if ($('#UserKind').val() == 'P') {
			$('#CompanyTaxno').parent('div').hide();
		} else {
			$('#CompanyTaxno').parent('div').show();
		}
	}
	
	toggleUserKind();
});