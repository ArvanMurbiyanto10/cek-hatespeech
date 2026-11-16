import { NextResponse } from 'next/server';
import { akhiriSesi } from '@/lib/auth';

export async function POST(req: Request) {
  await akhiriSesi();
  return NextResponse.redirect(new URL('/', req.url), { status: 303 });
}
