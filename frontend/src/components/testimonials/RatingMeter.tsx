export default function RatingMeter({ value }: { value: number }) {
  return (
    <div
      className="flex flex-col items-end gap-1.5"
      role="img"
      aria-label={`Rated ${value} out of 5`}
    >
      <p className="font-display leading-none text-sand">
        <span className="text-3xl md:text-4xl">{value}</span>
        <span className="ml-0.5 text-xs text-stone md:text-sm">/5</span>
      </p>
      <div className="flex gap-1" aria-hidden="true">
        {[1, 2, 3, 4, 5].map((i) => (
          <span
            key={i}
            className={`h-[2px] w-4 md:w-5 ${i <= value ? "bg-sand" : "bg-white/20"}`}
          />
        ))}
      </div>
    </div>
  );
}
