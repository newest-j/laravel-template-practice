export function browserTimeZone(): string {
  //   new Intl.DateTimeFormat()
  // Creates a formatter using the browser’s locale & settings
  // resolvedOptions()
  // Returns the actual settings the browser is using internally
  //   {
  //   locale: "en-NG",
  //   timeZone: "Africa/Lagos",
  //   calendar: "gregory",
  //   numberingSystem: "latn"
  // }
  // else it is just the string utc
  return new Intl.DateTimeFormat().resolvedOptions().timeZone || "UTC";
}

export function formatTimestamp(
  iso?: string | null,
  timeZone?: string,
  //   js built type check
  //   const opts = {
  //   year: "numeric",
  //   month: "long",
  //   day: "2-digit",
  // };
  // Intl.DateTimeFormatOptions is just a typescript interface type definition
  opts?: Intl.DateTimeFormatOptions
): string {
  if (!iso) return "";
  // this undefined means use the default browser set locale
  // instead of en-Us or fr-Fr this is specified
  // Intl.DateTimeFormat(locale, options)
  //  Intl.DateTimeFormat () this line defines how you want your datetime to look like
  return new Intl.DateTimeFormat(undefined, {
    dateStyle: "medium",
    timeStyle: "short",
    // oh so the spead operator is used to merge the value
    //  from the timezone object into the main object
    ...(timeZone ? { timeZone } : {}),
    ...(opts || {}),
  }).format(new Date(iso));
}
