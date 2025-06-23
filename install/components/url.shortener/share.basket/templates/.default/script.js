document.addEventListener('DOMContentLoaded', function () {
    const shareButton = document.getElementById('shareButton');
    const sharePopup = document.getElementById('sharePopup');
    const closePopup = document.getElementById('closePopup');
    const copyButton = document.getElementById('copyButton');
    const shareLink = document.getElementById('shareLink');
    const notification = document.getElementById('notification');
    const notificationShare = document.getElementById('notificationShare');

    shareButton.addEventListener('click', function (e) {
        e.stopPropagation();
        var request = BX.ajax.runComponentAction('url.shortener:share.basket', 'generateLink', {
            mode: 'class',
        });
        request.catch(function(response) {
            var errorMessage = response.errors[0].message;
            notificationShare.innerHTML = errorMessage;
            notificationShare.style.opacity = '1';
            setTimeout(function () {
                notificationShare.style.opacity = '0';
            }, 2000);

        });
        request.then(function (response) {
            sharePopup.style.display = 'block';
            shareLink.value = response.data.code;
        });
    });


    closePopup.addEventListener('click', function (e) {
        e.stopPropagation();
        sharePopup.style.display = 'none';
    });


    document.addEventListener('click', function (event) {
        if (!sharePopup.contains(event.target) && event.target !== shareButton) {
            sharePopup.style.display = 'none';
        }
    });


    copyButton.addEventListener('click', function () {
        shareLink.select();
        document.execCommand('copy');
        notification.style.opacity = '1';
        sharePopup.style.display = 'none';
        setTimeout(function () {
            notification.style.opacity = '0';
        }, 2000);
    });
});