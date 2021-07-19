// Only execute on daily menu template
if (document.querySelector("input[name=template]").value === 'collections/menus/dailyMenu') {
    const title = document.querySelector("input[name=title]");
    const publishDate = document.querySelector("input[name=published_date]");
    const metaDescription = document.querySelector("textarea[name=meta_description]");
    const pitchDate = document.querySelector("input[value=pitchDate]").nextElementSibling;
    const pitchLocation = document.querySelector("input[value=pitchLocation]").nextElementSibling;
    const inputEvent = new Event("input", {"bubbles": true});

    title.setAttribute('readonly', true);

    const formatUKDateString = function(isoDate) {
        try {
            let date = new Date(isoDate);
            return new Intl.DateTimeFormat('en-GB', {dateStyle: 'long', timeZone: 'UTC' }).format(date);
        } catch (error) {
            return '';
        }
    }

    const pitchDateAction = function () {
        publishDate.value = pitchDate.value;
        let fDate = formatUKDateString(pitchDate.value);
        title.value = pitchLocation.value + " " + fDate;
        metaDescription.value = metaText(pitchLocation.value, fDate);
        title.dispatchEvent(inputEvent);
    }

    const pitchLocationAction = function () {
        let fDate = formatUKDateString(pitchDate.value);
        title.value = pitchLocation.value + " " + fDate;
        metaDescription.value = metaText(pitchLocation.value, fDate);
        title.dispatchEvent(inputEvent);
    }

    const metaText = function (loc, date) {
        return `At MYPIE we only source the freshest ingredients to bring you the highest quality British pies, epic sides, and sausage rolls. Take a look at what we have to offer at ${loc} on ${date}.`;
    }

    pitchDate.addEventListener("input", pitchDateAction, false);
    pitchLocation.addEventListener("input", pitchLocationAction, false);
}
