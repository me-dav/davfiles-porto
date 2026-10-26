export default function EmailHint({
  initial,
  domain,
}: {
  initial: string;
  domain: string;
}) {
  return (
    <>
      <span className="sr-only">Email hidden, domain {domain}</span>
      <span
        aria-hidden="true"
        className="inline-flex items-center gap-1 font-body text-xs leading-none text-stone"
      >
        <span>{initial}</span>
        <span className="inline-flex gap-[3px]">
          {[0, 1, 2, 3].map((i) => (
            <span
              key={i}
              className="h-1 w-1 rounded-full bg-current opacity-70"
            />
          ))}
        </span>
        <span>@{domain}</span>
      </span>
    </>
  );
}
