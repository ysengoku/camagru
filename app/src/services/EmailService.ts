import nodemailer from 'nodemailer';
import SMTPTransport from 'nodemailer/lib/smtp-transport';

export class EmailService {
  private static instance: EmailService | null = null;
  private transporter: nodemailer.Transporter<SMTPTransport.SentMessageInfo>;

  private baseUrl: string = '';
  private options: SMTPTransport.Options = {
    host: process.env.SMTP_HOST,
    port: Number(process.env.SMTP_PORT || 465),
    secure: true,
    auth: {
      user: process.env.SMTP_USER,
      pass: process.env.SMTP_PASS,
    },
  };

  private constructor() {
    this.baseUrl = process.env.NODE_ENV === 'production'
      ? process.env.APP_BASE_URL_PROD || 'http://localhost:8080'
      : process.env.APP_BASE_URL_DEV || 'https://localhost:8443';
    this.transporter = nodemailer.createTransport(this.options);
  }

  public static getInstance(): EmailService {
    if (!EmailService.instance) {
      EmailService.instance = new EmailService();
    }
    return EmailService.instance;
  }

  public async sendConfirmationEmail(to: string, token: string): Promise<{success: boolean, error?: string}> {
    const link = `${this.baseUrl}/verify-email?token=${token}`;
    const html = `
      <p>Welcome to Camagru.</p>
      <p>Please confirm your email by clicking the link below:</p>
      <p><a href="${link}">${link}</a></p>
      <p>If you didn't request this, ignore this email.</p>
    `;
    const mail = {
      from: `Camagru <${this.options.auth?.user}>`,
      to: to,
      subject: 'Please confirm your email',
      html: html,
    }
    try {
      const info = await this.transporter.sendMail(mail);
      console.log('Email sent successfully.', info);
      return { success: true };
    } catch (error) {
      console.error('Error while sending an email', error);
      const message = error instanceof Error ? error.message : String(error);
      return { success: false, error: message };
    }
  }
}
