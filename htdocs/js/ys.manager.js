(function(){
	//constructor Brand	  
	function Brand(){}
	Brand.prototype={
		init:function(brand){
			this.getBrandsURL="/manager/admin.php?type=yourstyle&action=suggestBrands";
			$brand=$(brand);
			var $select=$("<div class='ys_select brand'>" +
				"<div class='fon'></div>" +
				"<div class='container'>" +
					"<ul>" +
					"</ul>" +
				"</div>" +
			"</div>");
			$brand.after($select);
			var $list=$select.find('ul');
			
			
		$brand.bind("click", function(e){
			e.stopPropagation();					
		});
		
		$brand.bind("keyup focus", $.proxy(function() {			
			$.ajax({
				url: this.getBrandsURL+'&q='+$brand.attr('value'),
				type:'GET',
				dataType: "json",
				success:$.proxy(function(data) {
					$list.empty();
					if(data == null){
						if($select.hasClass('ys_selectrel')) $select.removeClass('ys_selectrel');		
					}
					else{
						$(data).each(function(indx, el){
							$list.append($('<li />').attr('id', el.id).append(el.brand).click(function(){
								var val=el.brand.replace(/<\/?strong[^>]*>/g,'')
								$brand.attr('value', val);
								$select.removeClass('ys_selectrel');
							}));
						});
						if(!$select.hasClass('ys_selectrel')) $select.addClass('ys_selectrel');	
					}
				}, this)
			})								  
		}, this));
		$(document).bind("click", function(e) {
			if($select.hasClass('ys_selectrel')) $select.removeClass('ys_selectrel');			
    	});
			
			
			
			
			
			
			
			
			
			
			
			
			
		}
	}
		  
		  
	
	///
	$(window).bind("load", function(e) {
		//brand
		$(document).find("input[name='brand']").each(function(){new Brand().init(this)})
	});
})();
   
   
   
   
   
