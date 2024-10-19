export function useFormat(dateString) {
    var options = {  year: 'numeric', month: 'long', day: 'numeric' };
    var date  = new Date(dateString);
    return date.toLocaleDateString("en-US", options);
}
