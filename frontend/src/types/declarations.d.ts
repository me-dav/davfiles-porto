// src/types/declarations.d.ts
// Memberitahu TypeScript bahwa import file CSS itu valid
// Ini fix untuk error ts(2882): Cannot find module '*.css'

declare module '*.css' {
  const content: { [className: string]: string }
  export default content
}