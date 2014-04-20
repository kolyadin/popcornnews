/* ====== Title: Глупая листалка ====== */

$(function() {
	isNumber =  function(v){
		return typeof v === 'number' && isFinite(v);
	}

	slidePhotoInList = function() {	
		var currentElem = photoList[currentPhotoInList];
		var newImage = new Image();
		var tstload;	
		
		currentPhotoInList == 0 ? $('.entryArrowsLeft').hide() : $('.entryArrowsLeft').show();
		window.location.hash = parseInt(currentPhotoInList) + 1;
		
		$('.entryImg').removeClass('loaded');
		newImage.src = currentElem.img.replace('\/', '/');		
		newImage.onLoad = function() {
			var oldImg = $('.entryGal').find('img:visible');
			var maxContHeight = 0;
			
			function animateIt () {
				var tmpImg = $(newImage);
				tmpImg
					.css('display', 'none')
					.appendTo('.entryGal')
					.fadeIn(function() {
						if (currentElem.zoomable) {							
							$('.entryGal')
								.attr('href', newImage.src)
								.CloudZoom();
								
							$zoomblock = $('<div class="img_zoom show"><img src="/i/zooom.png"></div>');
							$('.entryImg #wrap').append($zoomblock);
						} else {
							$('.entryGal').removeAttr('href');
							$('.mousetrap').remove();
							$('.img_zoom').remove();
						}			
						
						$('.entryImg').addClass('loaded');							
					});	
							
				$('.entryCounterCurrent').text(currentPhotoInList+1);																											
			}
			
			function doResize() {	
				$('.entryDate').text(currentElem.date);	
				$('.entryLead').html(currentElem.description);	
				$('.entryTitle').text(currentElem.title);	
				$('.entrySource').text(currentElem.source);	
				
				$('span[class^="entryNum"]').remove();
				
				var tags = '<span class="entryNum_'+ currentPhotoInList + '">';			
				for (var i=0; i<currentElem.persons.length; i++) {
					if ((i==0) && (!$('.newsTrack .newsMeta .tags a').length)) {
						tags = tags + '<a href="'+ currentElem.persons[i].link +'">'+ currentElem.persons[i].name +'</a>';
					} else {
						tags = tags + ', <a href="'+ currentElem.persons[i].link +'">'+ currentElem.persons[i].name +'</a>';
					}
				}				
				tags = tags + "</span>";		
				
				if ($('.newsTrack .newsMeta .tags a').length) {
					$('.newsTrack .newsMeta .tags a:last').after( $(tags) );
				} else {
					$('.newsTrack .newsMeta .tags').append( $(tags) );
				}
				
				if ($('.newsTrack .newsMeta .tags a').length) {
					$('.tagsTitle').show();
				} else {
					$('.tagsTitle').hide();				
				}
					
				if ($('.entryGal').find('img:visible').length) {
					$('.entryGal')
						.find('img:visible')
							.fadeOut()
							.remove();
							
					animateIt();	
				} else {
					animateIt();
				}

			}

			doResize();	

		}(currentElem)
	}

	$('.entryArrowsLeft').click(function() {
		currentPhotoInList--;
		
		slidePhotoInList();
	})
	
	$('.entryArrowsRight').click(function() {
		currentPhotoInList++;
		if (currentPhotoInList > photoList.length-1) {
			window.location.href="/photo-articles";
		}

		slidePhotoInList();
	}) 		
	
	if (isNumber(parseInt(window.location.hash.split('#')[1]))) {
		var hashNumber = parseInt(window.location.hash.split('#')[1]);
		if ( hashNumber <= photoList.length) {
			if (hashNumber < 1) {
				hashNumber = 1;
			}
		
			currentPhotoInList = hashNumber - 1;
		}
	}
		
	slidePhotoInList();  						
	$('.theresNoJS').remove();	
})	