/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


$(function() {
    $("#id_participants_attendance").multipanetransfer({height: '200px'});
    $("#id_participants_attendance").parent().css('margin', '10px').css('width', '100%');
    $("#id_guest_members").select2({placeholder: "No Guests",});
});


