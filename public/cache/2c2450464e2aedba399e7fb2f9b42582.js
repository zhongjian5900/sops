(function getCity(){
 	var sltApartment=document.getElementById("apartment");
 	var sltPosition=document.getElementById("position");
 	var apartmentPosition=position[sltApartment.selectedIndex-1];
 	sltPosition.length=1;
 	// for(var i=0;i<position.length;i++){
 	// 	sltApartment[i+1]=new Option(position[i],position[i]]);
 	// }
 	for(var i=0;i<apartmentPosition.length;i++){
 		sltPosition[i+1]=new Option(apartmentPosition[i],apartmentPosition[i]);
 	}
})(jQuery);
