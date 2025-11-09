export function browserTimeZone(): string {
  // Intl.DateTimeFormat() create an object with tools for my date time format for my locale
  // .resolvedOptions() with this it reveals the settings that are availabe like the timezone(Africa/Lagos) locale
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
  return new Intl.DateTimeFormat(undefined, {
    dateStyle: "medium",
    timeStyle: "short",
    // oh so the spead operator is used to merge the value
    //  from the timezone object into the main object
    ...(timeZone ? { timeZone } : {}),
    ...(opts || {}),
  }).format(new Date(iso));
}
